<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

/**
 * Filter type for strings.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class TextFilterType extends AbstractFilterType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if (true === $options['compound']) {
            $builder->add('condition_pattern', 'choice', $options['choice_options']);
            $builder->add('text', 'text', $options['text_options']);

        } else {
            $builder->setAttribute('filter_options', array(
                'condition_pattern' => $options['condition_pattern'],
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
            return $options['condition_pattern'] == FilterOperands::OPERAND_SELECTOR;
        };

        $transformerId = function (Options $options) {
            return $options['compound'] ? 'lexik_form_filter.transformer.text' : 'lexik_form_filter.transformer.default';
        };

        $resolver
            ->setDefaults(array(
                'condition_pattern' => FilterOperands::STRING_EQUALS,
                'compound'          => $compound,
                'text_options'      => array(
                    'required' => false,
                    'trim'     => true,
                 ),
                'choice_options'    => array(
                   'choices'  => FilterOperands::getStringOperandsChoices(),
                   'required' => false,
                   'translation_domain' => 'LexikFormFilterBundle'
                 ),
                'transformer_id' => $transformerId,
            ))
            ->setAllowedValues(array(
                'transformer_id'    => array('lexik_form_filter.transformer.text','lexik_form_filter.transformer.default'),
                'condition_pattern' => FilterOperands::getStringOperands(true),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_text';
    }
}
