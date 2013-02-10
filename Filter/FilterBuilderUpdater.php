<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterTypeSharedableInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Transformer\TransformerAggregatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Transformer\FilterTransformerInterface;
use Lexik\Bundle\FormFilterBundle\Event\FilterEvents;
use Lexik\Bundle\FormFilterBundle\Event\PrepareEvent;
use Lexik\Bundle\FormFilterBundle\Event\ApplyFilterEvent;

/**
 * Build a query from a given form object, we basically add conditions to the Doctrine query builder.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
class FilterBuilderUpdater implements FilterBuilderUpdaterInterface
{
    /**
     * @var Lexik\Bundle\FormFilterBundle\Filter\Transformer\TransformerAggregatorInterface
     */
    protected $filterTransformerAggregator;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var array
     */
    protected $parts;

    /**
     * Constructor
     *
     * @param TransformerAggregatorInterface $filterTransformerAggregator
     * @param EventDispatcherInterface       $dispatcher
     */
    public function __construct(TransformerAggregatorInterface $filterTransformerAggregator, EventDispatcherInterface $dispatcher)
    {
        $this->filterTransformerAggregator = $filterTransformerAggregator;
        $this->dispatcher                  = $dispatcher;
        $this->parts                       = array();
    }

    /**
     * Set joins aliases.
     *
     * @param array $parts
     */
    public function setParts(array $parts)
    {
        $this->parts = $parts;
    }

    /**
     * Build a filter query.
     *
     * @param  FormInterface $form
     * @param  object $queryBuilder
     * @param  string|null $alias
     *
     * @return object filter builder
     */
    public function addFilterConditions(FormInterface $form, $queryBuilder, $alias = null)
    {
        $event = new PrepareEvent($queryBuilder);
        $this->dispatcher->dispatch(FilterEvents::PREPARE, $event);

        if ( ! $event->getFilterQuery() instanceof QueryInterface) {
            throw new \RuntimeException("Couldn't find any filter query object.");
        }

        if ( ! $alias) {
            $alias = $event->getFilterQuery()->getAlias();
            $this->parts[$alias] = '__root__';
        }

        $this->addFilters($form, $event->getFilterQuery(), $event->getFilterQuery()->getAlias(), $this->parts);

        return $queryBuilder;
    }

    /**
     * Add conditions on the filter builder instance.
     *
     * @param FormInterface $form
     * @param QueryInterface $filterQuery
     * @param string $alias
     * @param array $parts
     */
    protected function addFilters(FormInterface $form, QueryInterface $filterQuery, $alias = null, array &$parts = array())
    {
        /** @var $child FormInterface */
        foreach ($form->all() as $child) {
            $formType = $child->getConfig()->getType()->getInnerType();

            if ($formType instanceof FilterTypeSharedableInterface) {
                $join = $alias . '.' . $child->getName();

                if (!isset($parts[$join])) {
                    $qbe = new FilterBuilderExecuter($filterQuery, $alias, $parts);
                    $formType->addShared($qbe);
                }

                if (count($parts)) {
                    $this->addFilters($child, $filterQuery, $parts[$join]);
                }
            } else {
                $this->applyFilterCondition($child, $formType, $filterQuery, $alias);
            }
        }
    }

    /**
     * Apply the condition through event dispatcher.
     *
     * @param FormInterface $form
     * @param AbstractType $formType
     * @param QueryInterface $filterQuery
     * @param string $alias
     */
    protected function applyFilterCondition(FormInterface $form, AbstractType $formType, QueryInterface $filterQuery, $alias)
    {
        $config = $form->getConfig();
        $values = $this->prepareFilterValues($form, $formType);
        $values += array('alias' => $alias);
        $field = $values['alias'] . '.' . $form->getName();

        // apply the filter by using the closure set with the 'apply_filter' option
        $callable = $config->getAttribute('apply_filter');

        if ($callable instanceof \Closure) {
            $callable($filterQuery, $field, $values);
        } else if (is_callable($callable)) {
            call_user_func($callable, $filterQuery, $field, $values);
        } else {
            $eventName = sprintf('lexik_form_filter.apply.%s.%s', $filterQuery->getEventPartName(), is_string($callable) ? $callable : $formType->getName());
            $event = new ApplyFilterEvent($filterQuery, $field, $values);
            $this->dispatcher->dispatch($eventName, $event);
        }
    }

    /**
     * Prepare all values needed to apply the filter
     *
     * @param  FormInterface $form
     * @return array
     */
    protected function prepareFilterValues(FormInterface $form)
    {
        $config      = $form->getConfig();
        $values      = array();
        $transformer = $this->filterTransformerAggregator->get($config->getOption('transformer_id'));
        $values      = $transformer->transform($form);

        if ($config->hasAttribute('filter_options')) {
            $values = array_merge($values, $config->getAttribute('filter_options'));
        }

        return $values;
    }
}
