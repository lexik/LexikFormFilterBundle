<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Form\Type;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;

/**
 * Filter type for strings.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class TextFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (true === $options['compound']) {
            $builder->add('condition_pattern', ChoiceType::class, $options['choice_options']);
            $builder->add('text', TextType::class, $options['text_options']);
        } else {
            $builder->setAttribute('filter_options', array(
                'condition_pattern' => $options['condition_pattern'],
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'required'               => false,
                'condition_pattern'      => FilterOperands::STRING_EQUALS,
                'compound'               => function (Options $options) {
                    return $options['condition_pattern'] == FilterOperands::OPERAND_SELECTOR;
                },
                'text_options'           => array(
                    'required' => false,
                    'trim'     => true,
                ),
                'choice_options'         => array(
                    'choices'            => FilterOperands::getStringOperandsChoices(),
                    'choices_as_values'  => true,
                    'required'           => false,
                    'translation_domain' => 'LexikFormFilterBundle',
                ),
                'data_extraction_method' => function (Options $options) {
                    return $options['compound'] ? 'text' : 'default';
                },
            ))
            ->setAllowedValues('data_extraction_method', array('text', 'default'))
            ->setAllowedValues('condition_pattern', FilterOperands::getStringOperands(true))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'filter_text';
    }
}
