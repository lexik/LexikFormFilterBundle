<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\ORM\Filters;

use Doctrine\ORM\QueryBuilder;

use Lexik\Bundle\FormFilterBundle\Filter\ORM\ORMFilter;
use Lexik\Bundle\FormFilterBundle\Filter\ORM\Expr;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;

/**
 * Filter type for strings.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class TextFilter extends ORMFilter
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_text';
    }
    /**
     * {@inheritdoc}
     */
    protected function apply(QueryBuilder $filterBuilder, Expr $expr, $field, array $values)
    {
        if ('' !== $values['value'] && null !== $values['value']) {
            $pattern = empty($values['condition_pattern']) ? FilterOperands::STRING_EQUALS : $values['condition_pattern'];
            $filterBuilder->andWhere($expr->stringLike($field, $values['value'], $pattern));
        }
    }
}
