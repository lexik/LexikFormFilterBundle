<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\ORM\Filters;

use Lexik\Bundle\FormFilterBundle\Filter\ORM\ORMFilter;
use Lexik\Bundle\FormFilterBundle\Filter\ORM\Expr;

use Doctrine\ORM\QueryBuilder;

/**
 * Filter type for numbers.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class NumberRangeFilter extends ORMFilter
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_number_range';
    }

    /**
     * {@inheritdoc}
     */
    protected function apply(QueryBuilder $filterBuilder, Expr $expr, $field, array $values)
    {
        $value = $values['value'];

        if (isset($value['left_number'][0])) {
            $leftCond   = $value['left_number']['condition_operator'];
            $leftValue  = $value['left_number'][0];

            $filterBuilder->andWhere($expr->$leftCond($field, $leftValue));
        }

        if (isset($value['right_number'][0])) {
            $rightCond  = $value['right_number']['condition_operator'];
            $rightValue = $value['right_number'][0];

            $filterBuilder->andWhere($expr->$rightCond($field, $rightValue));
        }
    }
}
