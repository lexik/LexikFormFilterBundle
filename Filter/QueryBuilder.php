<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

use Lexik\Bundle\FormFilterBundle\Filter\Extension\EmbeddedFilterInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterTypeInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Transformer\TransformerAggregator;
use Lexik\Bundle\FormFilterBundle\Tests\Filter\FilterTransformerTest;

use Symfony\Component\Form\Form;

/**
 * Build a query from a given form object, we basically add conditions to the Doctrine query builder.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class QueryBuilder
{
    /**
     * @var Lexik\Bundle\FormFilterBundle\Filter\Transformer\TransformerAggregator
     */
    protected $filterTransformerAggregator;

    /**
     * @var array
     */
    private $joins;

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
     * @param string $alias
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function buildQuery(Form $form, \Doctrine\ORM\QueryBuilder $queryBuilder, $alias = null)
    {
        if (null == $alias) {
            $alias = $queryBuilder->getRootAlias();
        }

        foreach ($form->getChildren() as $child) {
            if ($this->isEmbeddedFilter($child)) {
                $childAlias = $this->addJoin($queryBuilder, $child);
                $this->buildQuery($child, $queryBuilder, $childAlias);
            } else {
                $this->addFilterCondition($queryBuilder, $child, $alias);
            }
        }

        return $queryBuilder;
    }

    /**
     * Add a join on the doctrine query builder and return the alias.
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param Form $form
     * @return string
     */
    protected function addJoin(\Doctrine\ORM\QueryBuilder $queryBuilder, Form $form)
    {
        $relationName = $form->getName();
        $alias = substr($relationName, 0, 3);
        $join = $queryBuilder->getRootAlias().'.'.$relationName;

        if (!isset($this->joins[$join])) {
            $queryBuilder->leftJoin($join, $alias);
            $this->joins[$join] = $alias;
        } else {
            $alias = $this->joins[$join];
        }

        return $alias;
    }

    /**
     * Add a condition to the builder for the given form.
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param Form $form
     * @param string $alias
     */
    protected function addFilterCondition(\Doctrine\ORM\QueryBuilder $queryBuilder, Form $form, $alias)
    {
        $values = $this->prepareFilterValues($form);

        // apply the filter by using the closure set with the 'apply_filter' option
        if ($form->hasAttribute('apply_filter')) {
            $callable = $form->getAttribute('apply_filter');

            if ($callable instanceof \Closure) {
                $callable($queryBuilder, $alias, $form->getName(), $values);
            } else {
                call_user_func($callable, $queryBuilder, $alias, $form->getName(), $values);
            }
        } else {
            // if no closure we use the applyFilter() method from a FilterTypeInterface
            $type = $this->getFilterType($form->getTypes());

            if ($type instanceof FilterTypeInterface) {
                $type->applyFilter($queryBuilder, $alias, $form->getName(), $values);
            }
        }
    }

    /**
     * Prepare all values needed to apply the filter.
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

    /**
     * Returns true if the given form is an embedded form filter.
     *
     * @param Form $form
     * @return boolean
     */
    protected function isEmbeddedFilter(Form $form)
    {
        $types = array_reverse($form->getTypes());

        return (isset($types[0]) && $types[0] instanceof EmbeddedFilterInterface);
    }
}
