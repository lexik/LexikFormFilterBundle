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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('position', NumberRangeFilterType::class, array(
                'left_number_options'  => array('condition_operator' => FilterOperands::OPERATOR_GREATER_THAN),
                'right_number_options' => array('condition_operator' => FilterOperands::OPERATOR_LOWER_THAN),
            ))
            ->add('position_selector', NumberRangeFilterType::class, array(
                'left_number_options'  => array('condition_operator' => FilterOperands::OPERAND_SELECTOR),
                'right_number_options' => array('condition_operator' => FilterOperands::OPERAND_SELECTOR),
            ))
            ->add('default_position', NumberRangeFilterType::class)
            ->add('createdAt', DateRangeFilterType::class, array(
                'left_date_options'  => array('widget' => 'single_text'),
                'right_date_options' => array('widget' => 'choice'),
            ))
            ->add('updatedAt', DateTimeRangeFilterType::class, array(
                'left_datetime_options'  => array('date_widget' => 'single_text', 'time_widget' => 'single_text'),
                'right_datetime_options' => array(),
            ))
            ->add('startAt', DateRangeFilterType::class, array(
                'left_date_options' => array(
                    'widget' => 'single_text',
                    'model_timezone' => 'UTC',
                    'view_timezone' => 'Asia/Karachi'
                ),
                'right_date_options' => array(
                    'widget' => 'single_text',
                    'model_timezone' => 'UTC',
                    'view_timezone' => 'Asia/Karachi'
                ),
            ))
        ;
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }
}
