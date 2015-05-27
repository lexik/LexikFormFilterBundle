<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

use Lexik\Bundle\FormFilterBundle\Event\ApplyFilterConditionEvent;
use Lexik\Bundle\FormFilterBundle\Event\FilterEvents;
use Lexik\Bundle\FormFilterBundle\Event\GetFilterConditionEvent;
use Lexik\Bundle\FormFilterBundle\Event\PrepareEvent;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionBuilderInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionNodeInterface;
use Lexik\Bundle\FormFilterBundle\Filter\DataExtractor\FormDataExtractorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\CollectionAdapterFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\EmbeddedFilterTypeInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;

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
     * @var ConditionBuilder
     */
    protected $conditionBuilder;

    /**
     * Constructor.
     *
     * @param FormDataExtractorInterface $dataExtractor
     * @param EventDispatcherInterface   $dispatcher
     */
    public function __construct(FormDataExtractorInterface $dataExtractor, EventDispatcherInterface $dispatcher)
    {
        $this->dataExtractor = $dataExtractor;
        $this->dispatcher = $dispatcher;
        $this->parts = array();
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
     * @param FormInterface $form
     * @param object        $queryBuilder
     * @param string|null   $alias
     *
     * @return object filter builder
     *
     * @throws \RuntimeException
     */
    public function addFilterConditions(FormInterface $form, $queryBuilder, $alias = null)
    {
        // create the right QueryInterface object
        $event = new PrepareEvent($queryBuilder);
        $this->dispatcher->dispatch(FilterEvents::PREPARE, $event);

        if (!$event->getFilterQuery() instanceof QueryInterface) {
            throw new \RuntimeException("Couldn't find any filter query object.");
        }

        // init parts (= ['alias' -> 'joins'])
        if (!$alias) {
            $alias = $event->getFilterQuery()->getRootAlias();
            $this->parts[$alias] = '__root__'; // the root alias does not target a join
        }

        // get conditions nodes defined by the 'filter_condition_builder' option
        // and add filters condition for each node
        $this->conditionBuilder = $this->getConditionBuilder($form);
        $this->addFilters($form, $event->getFilterQuery(), $alias, $this->parts);

        // walk condition nodes to add condition on the query builder instance
        $name = sprintf('lexik_filter.apply_filters.%s', $event->getFilterQuery()->getEventPartName());
        $this->dispatcher->dispatch($name, new ApplyFilterConditionEvent($queryBuilder, $this->conditionBuilder));

        $this->conditionBuilder = null;

        return $queryBuilder;
    }

    /**
     * Add filter conditions on the condition node instance.
     *
     * @param FormInterface  $form
     * @param QueryInterface $filterQuery
     * @param string         $alias
     * @param array          $parts
     *
     * @throws \RuntimeException
     */
    protected function addFilters(FormInterface $form, QueryInterface $filterQuery, $alias = null, array &$parts = array())
    {
        /** @var $child FormInterface */
        foreach ($form->all() as $child) {
            $formType = $child->getConfig()->getType()->getInnerType();

            // this means we have a relation
            if ($child->getConfig()->hasAttribute('add_shared')) {
                $join = $alias.'.'.$child->getName();

                if (!isset($parts[$join])) {
                    $addSharedClosure = $child->getConfig()->getAttribute('add_shared');

                    if (!$addSharedClosure instanceof \Closure) {
                        throw new \RuntimeException('Please provide a closure to the "add_shared" option.');
                    }

                    $qbe = new FilterBuilderExecuter($filterQuery, $alias, $parts);
                    $addSharedClosure($qbe);
                }

                if (count($parts)) {
                    $isCollection = ($formType instanceof CollectionAdapterFilterType);

                    $this->addFilters($isCollection ? $child->get(0) : $child, $filterQuery, $parts[$join]);
                }

            // Doctrine2 embedded object case
            } elseif ($formType instanceof EmbeddedFilterTypeInterface) {
                $this->addFilters($child, $filterQuery, $alias.'.'.$child->getName());

            // default case
            } else {
                $condition = $this->getFilterCondition($child, $formType, $filterQuery, $alias);

                if ($condition instanceof ConditionInterface) {
                    $this->conditionBuilder->addCondition($condition);
                }
            }
        }
    }

    /**
     * Get the condition through event dispatcher.
     *
     * @param FormInterface  $form
     * @param AbstractType   $formType
     * @param QueryInterface $filterQuery
     * @param string         $alias
     *
     * @return ConditionInterface|null
     */
    protected function getFilterCondition(FormInterface $form, AbstractType $formType, QueryInterface $filterQuery, $alias)
    {
        $values = $this->prepareFilterValues($form, $formType);
        $values += array('alias' => $alias);
        $field = $values['alias'].'.'.$form->getName();

        $condition = null;

        // build a complete form name including parents
        $completeName = $form->getName();
        $parentForm = $form;
        do {
            $parentForm = $parentForm->getParent();
            if (!is_numeric($parentForm->getName())) { // skip collection numeric index
                $completeName = $parentForm->getName().'.'.$completeName;
            }
        } while (!$parentForm->isRoot());

        // apply the filter by using the closure set with the 'apply_filter' option
        $callable = $form->getConfig()->getAttribute('apply_filter');

        if (false === $callable) {
            return;
        }

        if ($callable instanceof \Closure) {
            $condition = $callable($filterQuery, $field, $values);
        } elseif (is_callable($callable)) {
            $condition = call_user_func($callable, $filterQuery, $field, $values);
        } else {
            // trigger a specific or a global event name
            $eventName = sprintf('lexik_form_filter.apply.%s.%s', $filterQuery->getEventPartName(), $completeName);
            if (!$this->dispatcher->hasListeners($eventName)) {
                $eventName = sprintf('lexik_form_filter.apply.%s.%s', $filterQuery->getEventPartName(), is_string($callable) ? $callable : $formType->getName());
            }

            $event = new GetFilterConditionEvent($filterQuery, $field, $values);
            $this->dispatcher->dispatch($eventName, $event);

            $condition = $event->getCondition();
        }

        // set condition path
        if ($condition instanceof ConditionInterface) {
            $condition->setName(
                trim(substr($completeName, strpos($completeName, '.')), '.') // remove first level
            );
        }

        return $condition;
    }

    /**
     * Prepare all values needed to apply the filter.
     *
     * @param FormInterface $form
     *
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
     * Get the conditon builder object for the given form.
     *
     * @param Form $form
     *
     * @return ConditionBuilderInterface
     */
    protected function getConditionBuilder(Form $form)
    {
        $builderClosure = $form->getConfig()->getAttribute('filter_condition_builder');

        $builder = new ConditionBuilder();

        if ($builderClosure instanceof \Closure) {
            $builderClosure($builder);
        } else {
            $this->buildDefaultConditionNode($form, $builder->root('AND'));
        }

        return $builder;
    }

    /**
     * Create a default node hierarchy by using AND operator.
     *
     * @param Form                   $form
     * @param ConditionNodeInterface $root
     * @param string                 $parentName
     */
    protected function buildDefaultConditionNode(Form $form, ConditionNodeInterface $root, $parentName = '')
    {
        foreach ($form->all() as $child) {
            if ($child->getConfig()->hasAttribute('add_shared')) {
                $isCollection = ($child->getConfig()->getType()->getInnerType() instanceof CollectionAdapterFilterType);

                $this->buildDefaultConditionNode(
                    $isCollection ? $child->get(0) : $child,
                    $root->andX(),
                    $child->getName()
                );
            } else {
                $name = ('' !== $parentName) ? $parentName.'.'.$child->getName() : $child->getName();
                $root->field($name);
            }
        }
    }
}
