<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterTypeInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Transformer\TransformerAggregator;
use Lexik\Bundle\FormFilterBundle\Tests\Filter\FilterTransformerTest;

use Symfony\Component\Form\Form;

/**
 * Build a query from a given form object, we basically add conditions to the Doctrine query builder.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class QueryBuilderUpdater
{
    /**
     * @var Lexik\Bundle\FormFilterBundle\Filter\Transformer\TransformerAggregator
     */
    protected $filterTransformerAggregator;

    /**
     * Constructor
     *
     * @param TransformerAggregator $filterTransformerAggregator
     */
    public function __construct(TransformerAggregator $filterTransformerAggregator)
    {
        $this->filterTransformerAggregator = $filterTransformerAggregator;
    }

    /**
     * Build a filter query.
     *
     * @param \Symfony\Component\Form\Form $form
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function addFilterConditions(Form $form, \Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        foreach ($form->getChildren() as $child) {
            $this->addFilterCondition($queryBuilder, $child);
        }

        return $queryBuilder;
    }

    /**
     * Add a condition to the builder for the given form.
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param Form $form
     */
    protected function addFilterCondition(\Doctrine\ORM\QueryBuilder $queryBuilder, Form $form)
    {
        $values = $this->prepareFilterValues($form);

        // apply the filter by using the closure set with the 'apply_filter' option
        if ($form->hasAttribute('apply_filter')) {
            $callable = $form->getAttribute('apply_filter');

            if ($callable instanceof \Closure) {
                $callable($queryBuilder, $form->getName(), $values);
            } else {
                call_user_func($callable, $queryBuilder, $form->getName(), $values);
            }
        } else {
            // if no closure we use the applyFilter() method from a FilterTypeInterface
            $type = $this->getFilterType($form->getTypes());

            if ($type instanceof FilterTypeInterface) {
                $type->applyFilter($queryBuilder, $form->getName(), $values);
            }
        }
    }

    /**
     * Prepare all values needed to apply the filer.
     *
     * @param Form $form
     * @return array
     */
    protected function prepareFilterValues(Form $form)
    {
        $values = array();
        $type = $this->getFilterType($form->getTypes());

        if ($type instanceof FilterTypeInterface) {
            $transformer = $this->filterTransformerAggregator->get($type->getTransformerId());
            $values = $transformer->transform($form);
        }

        if ($form->hasAttribute('filter_options')) {
            $values = array_merge($values, $form->getAttribute('filter_options'));
        }

        return $values;
    }

    /**
     * Returns the first FilterTypeInterface instance.
     *
     * @param array $types
     * @return Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterTypeInterface
     */
    protected function getFilterType(array $types)
    {
        $types = array_reverse($types);

        $type = null;
        $i = 0;

        while ($i<count($types) && null == $type) {
            $type = ($types[$i] instanceof FilterTypeInterface) ? $types[$i] : null;
            $i++;
        }

        return $type;
    }
}
