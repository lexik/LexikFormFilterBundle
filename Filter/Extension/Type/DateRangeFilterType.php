<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Lexik\Bundle\FormFilterBundle\Filter\Expr;

class DateRangeFilterType extends AbstractFilterType implements FilterTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('left_date', 'filter_date', $options['left_date']);
        $builder->add('right_date', 'filter_date', $options['right_date']);

        $builder->setAttribute('filter_value_keys', array(
            'left_date'  => $options['left_date'],
            'right_date' => $options['right_date'],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver
            ->setDefaults(array(
                'left_date'  => array(),
                'right_date' => array(),
                'transformer_id' => 'lexik_form_filter.transformer.value_keys',
            ))
            ->setAllowedValues(array(
                'transformer_id' => array('lexik_form_filter.transformer.value_keys'),
            ))                                
            ;
    }

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
    public function applyFilter(QueryBuilder $queryBuilder, Expr $expr, $field, array $values)
    {
        $value = $values['value'];
        if(isset($value['left_date'][0]) || $value['right_date'][0]){
            $queryBuilder->andWhere($expr->dateInRange($field, $value['left_date'][0], $value['right_date'][0]));
        }
    }
}
