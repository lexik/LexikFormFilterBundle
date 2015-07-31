<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;

/**
 * Form filter for tests.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class ItemFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['with_selector']) {
            $builder->add('name', 'filter_text', array(
                'apply_filter' => $options['disabled_name'] ? false : null,
            ));
            $builder->add('position', 'filter_number', array(
                'condition_operator' => FilterOperands::OPERATOR_GREATER_THAN,
            ));
        } else {
            $builder->add('name', 'filter_text', array(
                'condition_pattern' => FilterOperands::OPERAND_SELECTOR,
            ));
            $builder->add('position', 'filter_number', array(
                'condition_operator' => FilterOperands::OPERAND_SELECTOR,
            ));
        }

        $builder->add('enabled', $options['checkbox'] ? 'filter_checkbox' : 'filter_boolean');
        $builder->add('createdAt', $options['datetime'] ? 'filter_datetime' : 'filter_date');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'with_selector' => false,
            'checkbox'      => false,
            'datetime'      => false,
            'disabled_name' => false,
        ));
    }

    public function getName()
    {
        return 'item_filter';
    }
}
