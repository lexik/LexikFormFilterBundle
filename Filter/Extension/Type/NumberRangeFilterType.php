<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Lexik\Bundle\FormFilterBundle\Filter\Expr;

use Doctrine\ORM\QueryBuilder;

/**
 * Filter type for numbers.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class NumberRangeFilterType extends AbstractFilterType implements FilterTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('left_number', 'filter_number', $options['left_number']);
        $builder->add('right_number', 'filter_number', $options['right_number']);

        $builder->setAttribute('filter_value_keys', array(
            'left_number'  => $options['left_number'],
            'right_number' => $options['right_number']
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'left_number' => array('condition_operator' => NumberFilterType::OPERATOR_GREATER_THAN_EQUAL),
            'right_number' => array('condition_operator' => NumberFilterType::OPERATOR_LOWER_THAN_EQUAL),
        ));
    }

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
    public function getTransformerId()
    {
        return 'lexik_form_filter.transformer.value_keys';
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilter(QueryBuilder $queryBuilder, Expr $expr, $field, array $values)
    {
        $value = $values['value'];

        if (isset($value['left_number'][0])) {
            $leftCond   = $value['left_number']['condition_operator'];
            $leftValue  = $value['left_number'][0];
            
            $queryBuilder->andWhere($expr->$leftCond($field, $leftValue));
        }

        if (isset($value['right_number'][0])) {
            $rightCond  = $value['right_number']['condition_operator'];
            $rightValue = $value['right_number'][0];

            $queryBuilder->andWhere($expr->$rightCond($field, $rightValue));
        }
    }
}
