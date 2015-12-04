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
class DateRangeFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('left_date', DateFilterType::class, $options['left_date_options']);
        $builder->add('right_date', DateFilterType::class, $options['right_date_options']);

        $builder->setAttribute('filter_value_keys', array(
            'left_date'  => $options['left_date_options'],
            'right_date' => $options['right_date_options'],
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
                'left_date_options'      => array(),
                'right_date_options'     => array(),
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
        return 'filter_date_range';
    }
}
