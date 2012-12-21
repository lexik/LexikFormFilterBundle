<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Filter type for date range field.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class DateRangeFilterType extends AbstractFilterType
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
                'left_date'      => array(),
                'right_date'     => array(),
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
}
