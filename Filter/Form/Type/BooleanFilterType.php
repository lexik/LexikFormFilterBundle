<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Form\Type;

use Symfony\Component\Form\AbstractType;
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
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
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
                    self::VALUE_YES  => 'boolean.yes',
                    self::VALUE_NO   => 'boolean.no',
                ),
                'empty_value'            => 'boolean.yes_or_no',
                'translation_domain'     => 'LexikFormFilterBundle',
                'data_extraction_method' => 'default',
            ))
            ->setAllowedValues('data_extraction_method', array('default'))
        ;
    }
}
