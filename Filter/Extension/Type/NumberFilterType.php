<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Filter type for numbers.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class NumberFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if (true === $options['compound']) {
            // if the form is compound we don't need the NumberToLocalizedStringTransformer added in the parent type.
            $builder->resetViewTransformers();

            $builder->add('condition_operator', 'choice', $options['choice_options']);
            $builder->add('text', 'number', $options['number_options']);

        } else {
            $builder->setAttribute('filter_options', array(
                'condition_operator' => $options['condition_operator'],
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver
            ->setDefaults(array(
                'required'               => false,
                'condition_operator'     => FilterOperands::OPERATOR_EQUAL,
                'compound'               => function (Options $options) {
                    return $options['condition_operator'] == FilterOperands::OPERAND_SELECTOR;
                },
                'number_options'         => array(
                    'required' => false,
                ),
                'choice_options'         => array(
                    'choices'  => FilterOperands::getNumberOperandsChoices(),
                    'required' => false,
                    'translation_domain' => 'LexikFormFilterBundle'
                ),
                'data_extraction_method' => function (Options $options) {
                    return $options['compound'] ? 'text' : 'default';
                },
            ))
            ->setAllowedValues(array(
                'data_extraction_method' => array('text','default'),
                'condition_operator'     => FilterOperands::getNumberOperands(true),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'number';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_number';
    }
}
