<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Filter type for numbers.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class NumberFilterType extends AbstractFilterType
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

        $compound = function (Options $options) {
            return $options['condition_operator'] == FilterOperands::OPERAND_SELECTOR;
        };

        $transformerId = function (Options $options) {
            return $options['compound'] ? 'lexik_form_filter.transformer.text' : 'lexik_form_filter.transformer.default';
        };

        $resolver
            ->setDefaults(array(
                'condition_operator' => FilterOperands::OPERATOR_EQUAL,
                'compound'           => $compound,
                'number_options'     => array(
                    'required' => false,
                ),
                'choice_options'     => array(
                    'choices'  => FilterOperands::getNumberOperandsChoices(),
                    'required' => false,
                    'translation_domain' => 'LexikFormFilterBundle'
                ),
                'transformer_id' => $transformerId,
            ))
            ->setAllowedValues(array(
                'transformer_id'     => array('lexik_form_filter.transformer.text','lexik_form_filter.transformer.default'),
                'condition_operator' => FilterOperands::getNumberOperands(true),
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
