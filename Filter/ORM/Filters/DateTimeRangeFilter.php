<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\ORM\Filters;

use Doctrine\ORM\QueryBuilder;

use Lexik\Bundle\FormFilterBundle\Filter\ORM\ORMFilter;
use Lexik\Bundle\FormFilterBundle\Filter\ORM\Expr;

class DateTimeRangeFilter extends ORMFilter
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_datetime_range';
    }

    /**
     * {@inheritdoc}
     */
    protected function apply(QueryBuilder $filterBuilder, Expr $expr, $field, array $values)
    {
        $value = $values['value'];
        if(isset($value['left_datetime'][0]) || $value['right_datetime'][0]){
            $filterBuilder->andWhere($expr->datetimeInRange($field, $value['left_datetime'][0], $value['right_datetime'][0]));
        }
    }
}
