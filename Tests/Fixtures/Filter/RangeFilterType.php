<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;

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
            ->add('position', 'filter_number_range', array(
                'left_number' => array('condition_operator' => FilterOperands::OPERATOR_GREATER_THAN),
                'right_number' => array('condition_operator' => FilterOperands::OPERATOR_LOWER_THAN)
            ))
            ->add('default_position', 'filter_number_range')
            ->add('createdAt', 'filter_date_range', array(
                'left_date' => array('widget' => 'choice'),
                'right_date' => array('widget' => 'choice'),
            ))
            ->add('updatedAt', 'filter_datetime_range', array(
                'left_datetime' => array('date_widget' => 'single_text', 'time_widget' => 'single_text'),
                'right_datetime' => array(),
            ))
        ;
    }

    public function getName()
    {
        return 'item_filter';
    }
}
