<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Lexik\Bundle\FormFilterBundle\Filter\FilterInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterTypeSharedableInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Transformer\TransformerAggregatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Transformer\FilterTransformerInterface;
use Lexik\Bundle\FormFilterBundle\Event\FilterEvents;
use Lexik\Bundle\FormFilterBundle\Event\PrepareEvent;
use Lexik\Bundle\FormFilterBundle\Event\GetFilterEvent;

/**
 * Build a query from a given form object, we basically add conditions to the Doctrine query builder.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
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
     * @param  object $filterBuilder
     * @param  string|null $alias
     *
     * @return object filter builder
     */
    public function addFilterConditions(FormInterface $form, $filterBuilder, $alias = null)
    {
        $event = new PrepareEvent($filterBuilder);
        $this->dispatcher->dispatch(FilterEvents::PREPARE, $event);

        if (!$alias) {
            $alias = $event->getAlias();
            $this->parts[$alias] = '__root__';
        }
        $expr = $event->getExpr();
        $this->addFilters($form, $filterBuilder, $alias, $this->parts, $expr);

        return $filterBuilder;
    }

    /**
     * Add conditions on the filter builder instance.
     *
     * @param FormInterface $form
     * @param object $filterBuilder
     * @param string $alias
     * @param array $parts
     * @param Expr $expr
     */
    protected function addFilters(FormInterface $form, $filterBuilder, $alias = null, array &$parts = array(), $expr = null)
    {
        /** @var $child FormInterface */
        foreach ($form->all() as $child) {
            $formType = $child->getConfig()->getType()->getInnerType();

            if ($formType instanceof FilterTypeSharedableInterface) {
                $join = $alias . '.' . $child->getName();

                if (!isset($parts[$join])) {
                    $qbe = new FilterBuilderExecuter($filterBuilder, $alias, $expr, $parts);
                    $formType->addShared($qbe);
                }

                if (count($parts)) {
                    $this->addFilters($child, $filterBuilder, $parts[$join], $parts, $expr);
                }
            } else {
                $type = $this->getFilterType($child->getConfig(), $filterBuilder);

                if ($type instanceof FilterInterface) {
                    $this->applyFilterCondition($child, $type, $filterBuilder, $alias, $expr);
                }
            }
        }
    }

    /**
     * Apply the condition for one FilterInterface.
     *
     * @param FormInterface $form
     * @param FilterInterface $type
     * @param object $filterBuilder
     * @param string $alias
     * @param object $expr
     */
    protected function applyFilterCondition(FormInterface $form, FilterInterface $type, $filterBuilder, $alias, $expr)
    {
        $config = $form->getConfig();
        $values = $this->prepareFilterValues($form, $type);
        $values += array('alias' => $alias);
        $field = $values['alias'] . '.' . $form->getName();

        // apply the filter by using the closure set with the 'apply_filter' option
        if ($config->hasAttribute('apply_filter')) {
            $callable = $config->getAttribute('apply_filter');

            if ($callable instanceof \Closure) {
                $callable($filterBuilder, $expr, $field, $values);
            } else {
                call_user_func($callable, $filterBuilder, $expr, $field, $values);
            }
        } else {
            // if no closure we use the applyFilter() method from a FilterInterface
            $type->applyFilter($filterBuilder, $expr, $field, $values);
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

    /**
     * Get filter type name by form config
     *
     * @param FormConfigInterface $config
     *
     * @return string
     */
    protected function getFilterTypeName(FormConfigInterface $config)
    {
        $formType = $config->getType()->getInnerType();

        return ($config->hasAttribute('apply_filter') && is_string($config->getAttribute('apply_filter')))
            ? $config->getAttribute('apply_filter')
            : $formType->getName();
    }

    /**
     * Returns the filter type used to build the given form.
     *
     * @param FormConfigInterface $config
     * @param object              $filterBuilder
     *
     * @return FilterInterface
     */
    protected function getFilterType(FormConfigInterface $config, $filterBuilder)
    {
        $event = new GetFilterEvent($filterBuilder, $this->getFilterTypeName($config));
        $this->dispatcher->dispatch(FilterEvents::GET_FILTER, $event);

        return $event->getFilter();
    }
}
