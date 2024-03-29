<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Form\Type;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Filter type for numbers.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class NumberRangeFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('left_number', NumberFilterType::class, $options['left_number_options']);
        $builder->add('right_number', NumberFilterType::class, $options['right_number_options']);

        $builder->setAttribute('filter_value_keys', ['left_number' => $options['left_number_options'], 'right_number' => $options['right_number_options']]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(['required' => false, 'left_number_options' => ['condition_operator' => FilterOperands::OPERATOR_GREATER_THAN_EQUAL], 'right_number_options' => ['condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL], 'data_extraction_method' => 'value_keys'])
            ->setAllowedValues('data_extraction_method', ['value_keys'])
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'filter_number_range';
    }
}
