<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Lexik\Bundle\FormFilterBundle\Filter\Expr;

/**
 * Filter type for boolean.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class CheckboxFilterType extends AbstractFilterType implements FilterTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'checkbox';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_checkbox';
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformerId()
    {
        return 'lexik_form_filter.transformer.default';
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilter(QueryBuilder $queryBuilder, Expr $expr, $field, array $values)
    {
        if (!empty($values['value'])) {
            $queryBuilder->andWhere($expr->eq($field, $values['value']));
        }
    }
}
