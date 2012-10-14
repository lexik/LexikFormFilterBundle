<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\ORM\Filters;

use Doctrine\ORM\QueryBuilder;

use Lexik\Bundle\FormFilterBundle\Filter\ORM\ORMFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\ORM\Expr;

/**
 * Filter type for strings.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class TextFilterType extends ORMFilterType
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
        if (!empty($values['value'])) {
            $filterBuilder->andWhere($expr->stringLike($field, $values['value'], $values['condition_pattern']));
        }
    }
}
