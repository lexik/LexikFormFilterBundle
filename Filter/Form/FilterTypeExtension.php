<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FormType;
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
        if (null !== $options['apply_filter']) {
            $builder->setAttribute('apply_filter', $options['apply_filter']);
        }

        if ($options['filter_condition_builder'] instanceof \Closure) {
            $builder->setAttribute('filter_condition_builder', $options['filter_condition_builder']);
        }

        if (null !== $options['filter_field_name']) {
            $builder->setAttribute('filter_field_name', $options['filter_field_name']);
        }

        if (null !== $options['filter_shared_name']) {
            $builder->setAttribute('filter_shared_name', $options['filter_shared_name']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'apply_filter'             => null,
            'data_extraction_method'   => 'default',
            'filter_condition_builder' => null,
            'filter_field_name'        => null,
            'filter_shared_name'       => null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return FormType::class;
    }

    /**
     * @return iterable
     */
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
