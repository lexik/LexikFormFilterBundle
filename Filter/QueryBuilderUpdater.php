<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterTypeInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Transformer\TransformerAggregatorInterface;
use Lexik\Bundle\FormFilterBundle\Tests\Filter\FilterTransformerTest;

use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Build a query from a given form object, we basically add conditions to the Doctrine query builder.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class QueryBuilderUpdater implements QueryBuilderUpdaterInterface
{
    /**
     * @var TransformerAggregatorInterface
     */
    protected $filterTransformerAggregator;

    /**
     * @var Expr
     */
    protected $expr;

    /**
     * Constructor
     *
     * @param TransformerAggregatorInterface $filterTransformerAggregator
     */
    public function __construct(TransformerAggregatorInterface $filterTransformerAggregator)
    {
        $this->filterTransformerAggregator = $filterTransformerAggregator;
        $this->expr = new Expr;
    }

    /**
     * Build a filter query.
     *
     * @param  FormInterface $form
     * @param  QueryBuilder $queryBuilder
     * @return QueryBuilder
     */
    public function addFilterConditions(FormInterface $form, QueryBuilder $queryBuilder)
    {
        foreach ($form->all() as $child) {
            $this->addFilterCondition($child, $queryBuilder);
        }

        return $queryBuilder;
    }

    /**
     * Add a condition to the builder for the given form.
     *
     * @param FormInterface $form
     * @param QueryBuilder $queryBuilder
     */
    protected function addFilterCondition(FormInterface $form, QueryBuilder $queryBuilder)
    {
        $type = $this->getFilterType($form);

        if ($type) {
            $values  = $this->prepareFilterValues($form);
            $aliases = $queryBuilder->getRootAliases();
            $values += array('alias' => (isset($aliases[0]) ? $aliases[0] : null));
            $alias = $values['alias'];

            $field = ($alias ? ($alias . '.') : '') . $form->getName();

            $type->applyFilter($queryBuilder, $this->expr, $field, $values);
        }
    }

    /**
     * Prepare all values needed to apply the filer.
     *
     * @param  FormInterface $form
     * @return array
     */
    protected function prepareFilterValues(FormInterface $form)
    {
        $values = array();
        $type   = $this->getFilterType($form);

        if ($type) {
            $transformer = $this->filterTransformerAggregator->get($type->getTransformerId());
            $values      = $transformer->transform($form);
        }

        if ($form->getConfig()->hasAttribute('filter_options')) {
            $values = array_merge($values, $form->getConfig()->getAttribute('filter_options'));
        }

        return $values;
    }

    /**
     * Returns the first FilterTypeInterface instance
     *
     * Each form field has hierarchy of form types.
     * Get first form type which realizes FilterTypeInterface
     *
     * @param  FormInterface $form
     * @return FilterTypeInterface|null
     */
    protected function getFilterType(FormInterface $form)
    {
        $filterType = null;
        $config     = $form->getConfig();
        $types      = $config->getTypes();

        foreach (array_reverse($types) as $type) {
            if ($type instanceof FilterTypeInterface) {
                $filterType = $type;
                break;
            }
        }

        return $filterType;
    }
}
