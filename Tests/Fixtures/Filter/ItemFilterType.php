<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\BooleanFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\CheckboxFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateTimeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\NumberFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;

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
            $builder->add('name', TextFilterType::class, array(
                'apply_filter' => $options['disabled_name'] ? false : null,
            ));
            $builder->add('position', NumberFilterType::class, array(
                'condition_operator' => FilterOperands::OPERATOR_GREATER_THAN,
            ));
        } else {
            $builder->add('name', TextFilterType::class, array(
                'condition_pattern' => FilterOperands::OPERAND_SELECTOR,
            ));
            $builder->add('position', NumberFilterType::class, array(
                'condition_operator' => FilterOperands::OPERAND_SELECTOR,
            ));
        }

        $builder->add('enabled', $options['checkbox'] ? CheckboxFilterType::class : BooleanFilterType::class);
        $builder->add('createdAt', $options['datetime'] ? DateTimeFilterType::class : DateFilterType::class);
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

    public function getBlockPrefix()
    {
        return 'item_filter';
    }
}
