<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;

/**
 * Define filtering options.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class FilterTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['apply_filter'] instanceof \Closure
            || is_callable($options['apply_filter'])
            || is_string($options['apply_filter'])
        ) {
            $builder->setAttribute('apply_filter', $options['apply_filter']);
        }

        if ($options['filter_condition_builder'] instanceof \Closure) {
            $builder->setAttribute('filter_condition_builder', $options['filter_condition_builder']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'apply_filter'             => null,
            'data_extraction_method'   => 'default',
            'filter_condition_builder' => null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}
