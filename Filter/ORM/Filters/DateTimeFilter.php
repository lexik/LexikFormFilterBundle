<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\ORM\Filters;

use Doctrine\ORM\QueryBuilder;

use Lexik\Bundle\FormFilterBundle\Filter\ORM\ORMFilter;
use Lexik\Bundle\FormFilterBundle\Filter\ORM\Expr;

class DateTimeFilter extends ORMFilter
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_datetime';
    }

    /**
     * {@inheritdoc}
     */
    protected function apply(QueryBuilder $filterBuilder, Expr $expr, $field, array $values)
    {
        if ($values['value'] instanceof \DateTime) {
            $date = $values['value']->format(Expr::SQL_DATE_TIME);
            $filterBuilder->andWhere($expr->eq($field, $expr->literal($date)));
        }
    }
}
