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
class QueryBuilder
{
    /**
     *
     * @var FilterTransformerAggregator
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
    public function buildQuery(Form $form, \Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        foreach ($form->getChildren() as $child) {
            $this->addFilerCondition($queryBuilder, $child);
        }

        return $queryBuilder;
    }

    /**
     * Add a condition to the builder for the given form.
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param Form $form
     */
    protected function addFilerCondition(\Doctrine\ORM\QueryBuilder $queryBuilder, Form $form)
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
            $types = array_reverse($form->getTypes());
            $filterApplied = false;
            $i = 0;

            while ($i<count($types) && !$filterApplied) {
                $type = $types[$i];

                if ($type instanceof FilterTypeInterface) {
                    $type->applyFilter($queryBuilder, $form->getName(), $values);
                    $filterApplied = true;
                }

                $i++;
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
        $data = $form->getData();

        $types = array_reverse($form->getTypes());
        $transformerApplied = false;
        $i = 0;

        while ($i<count($types) && !$transformerApplied) {
            $type = $types[$i];

            if ($type instanceof FilterTypeInterface) {
                $transformerId = $type->getTransformerId();

                /** @var FilterTransformerInterface */
                $transformer = $this->filterTransformerAggregator->get($transformerId);
                $values = $transformer->transform($form);
                $transformerApplied = true;
            }

            $i++;
        }

        if ($form->hasAttribute('filter_options')) {
            $values = array_merge($values, $form->getAttribute('filter_options'));
        }

        return $values;
    }
}
