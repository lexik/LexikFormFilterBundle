<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\ORM\Filters;

use Doctrine\ORM\QueryBuilder;

use Lexik\Bundle\FormFilterBundle\Filter\ORM\ORMFilter;
use Lexik\Bundle\FormFilterBundle\Filter\ORM\Expr;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\BooleanFilterType;

/**
 * Filter to use with boolean values.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class BooleanFilter extends ORMFilter
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_boolean';
    }

   /**
     * {@inheritdoc}
     */
    protected function apply(QueryBuilder $filterBuilder, Expr $expr, $field, array $values)
    {
        if (!empty($values['value'])) {
            $value = (int)(BooleanFilterType::VALUE_YES == $values['value']);
            $filterBuilder->andWhere($expr->eq($field, $value));
        }
    }
}
