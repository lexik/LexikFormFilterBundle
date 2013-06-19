<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\ORM\Filters;

use Doctrine\ORM\QueryBuilder;

use Lexik\Bundle\FormFilterBundle\Filter\ORM\ORMFilter;
use Lexik\Bundle\FormFilterBundle\Filter\ORM\Expr;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;

class EntityFieldFilter extends ORMFilter
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_entity_field';
    }

    /**
     * {@inheritdoc}
     */
    protected function apply(QueryBuilder $filterBuilder, Expr $expr, $field, array $values)
    {
        $field  = str_replace(':', '.', $field);
        $entity = explode('.', $field);
        $field  = $entity[2];
        $entity = $entity[1];
        $alias  = strtolower(substr($entity, 0, 1));
        $value  = $values['value'];
        $joined = implode('.', array($values['alias'], $entity));
        if (!isSet($values['value']) || !$values['value']) {
            return;
        }
        if ('' !== $values['value'] && null !== $values['value']) {
            $pattern = empty($values['condition_pattern']) ? FilterOperands::STRING_EQUALS : $values['condition_pattern'];
            $filterBuilder->leftJoin($joined, $alias);
            $filterBuilder->andWhere($expr->stringLike(implode('.', array($alias, $field)), $value, $pattern));
        }
    }
}
