<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Lexik\Bundle\FormFilterBundle\Filter\Expr;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Filter type for select list.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class ChoiceFilterType extends ChoiceType implements FilterTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'filter';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_choice';
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
            $queryBuilder->andWhere($expr->eq($field, ':value'))
                         ->setParameter('value', $values['value']);
        }
    }
}
