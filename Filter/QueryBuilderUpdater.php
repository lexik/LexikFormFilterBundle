<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormConfigInterface;

use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterTypeInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterTypeSharedableInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Transformer\TransformerAggregatorInterface;
use Lexik\Bundle\FormFilterBundle\Tests\Filter\FilterTransformerTest;

use Doctrine\ORM\QueryBuilder;

/**
 * Build a query from a given form object, we basically add conditions to the Doctrine query builder.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class QueryBuilderUpdater implements QueryBuilderUpdaterInterface
{
    /**
     * @var Lexik\Bundle\FormFilterBundle\Filter\Transformer\TransformerAggregatorInterface
     */
    protected $filterTransformerAggregator;

    /**
     * @var Lexik\Bundle\FormFilterBundle\Filter\Expr
     */
    protected $expr;

    /**
     * @var array
     */
    protected $parts;

    /**
     * Constructor
     *
     * @param TransformerAggregatorInterface $filterTransformerAggregator
     */
    public function __construct(TransformerAggregatorInterface $filterTransformerAggregator)
    {
        $this->filterTransformerAggregator = $filterTransformerAggregator;
        $this->expr                        = new Expr();
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
     * @param  QueryBuilder $queryBuilder
     * @param  string|null $alias
     * @return QueryBuilder
     */
    public function addFilterConditions(FormInterface $form, QueryBuilder $queryBuilder, $alias = null)
    {
        if (!$alias) {
            $aliases = $queryBuilder->getRootAliases();
            $alias   = isset($aliases[0]) ? $aliases[0] : '';
            $this->parts[$alias] = '__root__';
        }

        /** @var $child FormInterface */
        foreach ($form->all() as $child) {
            $type = $this->getFilterType($child);

            if ($type instanceof FilterTypeInterface) {
                $this->applyFilterCondition($child, $type, $queryBuilder, $alias);

            } else if ($type instanceof FilterTypeSharedableInterface) {
                $join = $alias.'.'.$child->getName();

                if (!isset($this->parts[$join])) {
                    $qbe = new QueryBuilderExecuter($queryBuilder, $alias, $this->expr, $this->parts);
                    $type->addShared($qbe);
                }

                if (count($this->parts)) {
                    $childAlias = $this->parts[$join];
                    $this->addFilterConditions($child, $queryBuilder, $childAlias, $this->parts);
                }
            }
        }

        return $queryBuilder;
    }

    /**
     * Apply the condition for one FilterTypeInterface.
     *
     * @param FormInterface $form
     * @param FilterTypeInterface $type
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     */
    protected function applyFilterCondition(FormInterface $form, FilterTypeInterface $type, QueryBuilder $queryBuilder, $alias)
    {
        $values = $this->prepareFilterValues($form, $type);
        $values += array('alias' => $alias);
        $field = $values['alias'] . '.' . $form->getName();

        $config = $form->getConfig();

        // apply the filter by using the closure set with the 'apply_filter' option
        if ($config->hasAttribute('apply_filter')) {
            $callable = $config->getAttribute('apply_filter');

            if ($callable instanceof \Closure) {
                $callable($queryBuilder, $this->expr, $field, $values);
            } else {
                call_user_func($callable, $queryBuilder, $this->expr, $field, $values);
            }
        } else {
            // if no closure we use the applyFilter() method from a FilterTypeInterface
            $type->applyFilter($queryBuilder, $this->expr, $field, $values);
        }
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

    /**
     * Returns the filter type used to build the given form.
     *
     * @param FormInterface $form
     * @return FilterTypeInterface
     */
    protected function getFilterType(FormInterface $form)
    {
        return $form->getConfig()->getType()->getInnerType();
    }
}
