<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\ORM\Filters;

use Doctrine\ORM\QueryBuilder;

use Lexik\Bundle\FormFilterBundle\Filter\ORM\ORMFilter;
use Lexik\Bundle\FormFilterBundle\Filter\ORM\Expr;

class DateRangeFilter extends ORMFilter
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_date_range';
    }

    /**
     * {@inheritdoc}
     */
    protected function apply(QueryBuilder $filterBuilder, Expr $expr, $field, array $values)
    {
        $value = $values['value'];
        if(isset($value['left_date'][0]) || $value['right_date'][0]){
            $filterBuilder->andWhere($expr->dateInRange($field, $value['left_date'][0], $value['right_date'][0]));
        }
    }
}
