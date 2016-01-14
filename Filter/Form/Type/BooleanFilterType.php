<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Filter to use with boolean values.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class BooleanFilterType extends AbstractType
{
    const VALUE_YES = 'y';
    const VALUE_NO  = 'n';

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'filter_boolean';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'required'               => false,
                'choices'                => array(
                    'boolean.yes' => self::VALUE_YES,
                    'boolean.no'  => self::VALUE_NO,
                ),
                'choices_as_values'      => true, // must be removed for use in Symfony 3.1, needed for 2.8
                'placeholder'            => 'boolean.yes_or_no',
                'translation_domain'     => 'LexikFormFilterBundle',
                'data_extraction_method' => 'default',
            ))
            ->setAllowedValues('data_extraction_method', array('default'))
        ;
    }
}
