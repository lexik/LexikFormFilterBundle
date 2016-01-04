<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Filter type for date range field.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class DateTimeRangeFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('left_datetime', DateTimeFilterType::class, $options['left_datetime_options']);
        $builder->add('right_datetime', DateTimeFilterType::class, $options['right_datetime_options']);

        $builder->setAttribute('filter_value_keys', array(
            'left_datetime'  => $options['left_datetime_options'],
            'right_datetime' => $options['right_datetime_options'],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'required'               => false,
                'left_datetime_options'  => array(),
                'right_datetime_options' => array(),
                'data_extraction_method' => 'value_keys',
            ))
            ->setAllowedValues('data_extraction_method', array('value_keys'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'filter_datetime_range';
    }
}
