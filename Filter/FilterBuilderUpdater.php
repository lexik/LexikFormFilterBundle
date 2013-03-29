<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Lexik\Bundle\FormFilterBundle\Filter\DataExtractor\FormDataExtractorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterTypeSharedableInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterInterface;
use Lexik\Bundle\FormFilterBundle\Filter\ORM\Expr;
use Lexik\Bundle\FormFilterBundle\Event\FilterEvents;
use Lexik\Bundle\FormFilterBundle\Event\PrepareEvent;
use Lexik\Bundle\FormFilterBundle\Event\ApplyFilterEvent;
use Lexik\Bundle\FormFilterBundle\Event\GetFilterEvent;

/**
 * Build a query from a given form object, we basically add conditions to the Doctrine query builder.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
class FilterBuilderUpdater implements FilterBuilderUpdaterInterface
{
    /**
     * @var FormDataExtractorInterface
     */
    protected $dataExtractor;

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
     * @param FormDataExtractorInterface $dataExtractor
     * @param EventDispatcherInterface   $dispatcher
     */
    public function __construct(FormDataExtractorInterface $dataExtractor, EventDispatcherInterface $dispatcher)
    {
        $this->dataExtractor = $dataExtractor;
        $this->dispatcher    = $dispatcher;
        $this->parts         = array();
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
     * @param  object        $queryBuilder
     * @param  string|null   $alias
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
     * Add conditions on the query builder instance.
     *
     * @param FormInterface  $form
     * @param QueryInterface $filterQuery
     * @param string         $alias
     * @param array          $parts
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
            // build specific event name including all form parent names
            $name = $form->getName();
            $parentForm = $form;
            do {
                $parentForm = $parentForm->getParent();
                $name = $parentForm->getName() . '.' . $name;
            } while ( ! $parentForm->isRoot());

            // trigger specific or global event name
            $eventName = sprintf('lexik_form_filter.apply.%s.%s', $filterQuery->getEventPartName(), $name);
            if ( ! $this->dispatcher->hasListeners($eventName)) {
                $eventName = sprintf('lexik_form_filter.apply.%s.%s', $filterQuery->getEventPartName(), is_string($callable) ? $callable : $formType->getName());
            }

            $event = new ApplyFilterEvent($filterQuery, $field, $values);
            $this->dispatcher->dispatch($eventName, $event);

            if ($this->dispatcher->hasListeners('lexik_filter.get')) {
                $type = $this->getFilterType($form->getConfig(), $filterQuery->getQueryBuilder());

                if ($type instanceof FilterInterface) {
                    $type->applyFilter($filterQuery->getQueryBuilder(), new Expr(), $field, $values);
                }
            }
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
        $config = $form->getConfig();
        $values = $this->dataExtractor->extractData($form, $config->getOption('data_extraction_method', 'default'));

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
     *
     * @deprecated Deprecated since version 2.0, to be removed in 2.1. Use EventDispatcher instead.
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
     *
     * @deprecated Deprecated since version 2.0, to be removed in 2.1. Use EventDispatcher instead.
     */
    protected function getFilterType(FormConfigInterface $config, $filterBuilder)
    {
        $event = new GetFilterEvent($filterBuilder, $this->getFilterTypeName($config));
        $this->dispatcher->dispatch(FilterEvents::GET_FILTER, $event);

        return $event->getFilter();
    }
}
