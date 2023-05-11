<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateTimeRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\NumberRangeFilterType;

/**
 * Form filter for tests.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class RangeFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', NumberRangeFilterType::class, ['left_number_options'  => ['condition_operator' => FilterOperands::OPERATOR_GREATER_THAN], 'right_number_options' => ['condition_operator' => FilterOperands::OPERATOR_LOWER_THAN]])
            ->add('position_selector', NumberRangeFilterType::class, ['left_number_options'  => ['condition_operator' => FilterOperands::OPERAND_SELECTOR], 'right_number_options' => ['condition_operator' => FilterOperands::OPERAND_SELECTOR]])
            ->add('default_position', NumberRangeFilterType::class)
            ->add('createdAt', DateRangeFilterType::class, ['left_date_options'  => ['widget' => 'single_text'], 'right_date_options' => ['widget' => 'choice', 'years' => range(2010, 2020)]])
            ->add('updatedAt', DateTimeRangeFilterType::class, ['left_datetime_options'  => ['date_widget' => 'single_text', 'time_widget' => 'single_text'], 'right_datetime_options' => ['years' => range(2010, 2020)]])
            ->add('startAt', DateRangeFilterType::class, ['left_date_options' => ['widget' => 'single_text', 'model_timezone' => 'UTC', 'view_timezone' => 'Asia/Karachi'], 'right_date_options' => ['widget' => 'single_text', 'model_timezone' => 'UTC', 'view_timezone' => 'Asia/Karachi']])
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'item_filter';
    }
}
