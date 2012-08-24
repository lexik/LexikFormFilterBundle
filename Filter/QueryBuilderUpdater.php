<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

use Symfony\Component\Form\FormTypeInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterTypeInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterTypeSharedableInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Transformer\TransformerAggregatorInterface;
use Lexik\Bundle\FormFilterBundle\Tests\Filter\FilterTransformerTest;

use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\QueryBuilder;
use Millwright\ConfigurationBundle\ORM\Expr;

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
        $this->expr                        = new Expr;
    }

    /**
     * Build a filter query.
     *
     * @param  FormInterface $form
     * @param  QueryBuilder $queryBuilder
     * @param  string|null $alias
     * @param  array & $parts
     * @return QueryBuilder
     */
    public function addFilterConditions(FormInterface $form, QueryBuilder $queryBuilder, $alias = null, array & $parts = array())
    {
        if (!$alias) {
            $aliases = $queryBuilder->getRootAliases();
            $alias   = isset($aliases[0]) ? $aliases[0] : '';
        }

        /** @var $child FormInterface */
        foreach ($form->all() as $child) {
            $config = $child->getConfig();
            $type   = $child->getConfig()->getType()->getInnerType();

            /** @var $type FilterTypeInterface */
            if ($type instanceof FilterTypeInterface) {
                $values = $this->prepareFilterValues($child, $type);
                $values += array('alias' => $alias);
                $field = $values['alias'] . '.' . $child->getName();
                $type->applyFilter($queryBuilder, $this->expr, $field, $values);
                break;
            } else if ($type instanceof FilterTypeSharedableInterface) {
                $qbe = new QueryBuilderExecuter($queryBuilder, $alias, $this->expr, $parts);
                $type->addShared($qbe);

                if (count($parts)) {
                    $partsKeys  = array_keys($parts);
                    $childAlias = end($partsKeys);
                    var_dump('1');
                    $this->addFilterConditions($child, $queryBuilder, $childAlias, $parts);
                }
                break;
            }
        }

        return $queryBuilder;
    }

    /**
     * Prepare all values needed to apply the filter
     *
     * @param  FormInterface $form
     * @param  FilterTypeInterface $type
     * @return array
     */
    protected function prepareFilterValues(FormInterface $form, FilterTypeInterface $type)
    {
        $values      = array();
        $transformer = $this->filterTransformerAggregator->get($type->getTransformerId());
        $values      = $transformer->transform($form);
        $config = $form->getConfig();

        if ($config->hasAttribute('filter_options')) {
            $values = array_merge($values, $config->getAttribute('filter_options'));
        }

        return $values;
    }
}
