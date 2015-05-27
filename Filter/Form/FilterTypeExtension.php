<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Form;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
        if (null !== $options['apply_filter']) {
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
            'apply_filter' => null,
            'data_extraction_method' => 'default',
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
