<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Form\Type;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        if (true === $options['compound']) {
            // if the form is compound we don't need the NumberToLocalizedStringTransformer added in the parent type.
            $builder->resetViewTransformers();

            $builder->add('condition_operator', ChoiceType::class, $options['choice_options']);
            $builder->add('text', NumberType::class, $options['number_options']);
        } else {
            $builder->setAttribute('filter_options', array(
                'condition_operator' => $options['condition_operator'],
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $defaults = array(
                'required'               => false,
                'condition_operator'     => FilterOperands::OPERATOR_EQUAL,
                'compound'               => function (Options $options) {
                    return $options['condition_operator'] == FilterOperands::OPERAND_SELECTOR;
                },
                'number_options'         => array(
                    'required' => false,
                ),
                'choice_options'         => array(
                    'choices'            => FilterOperands::getNumberOperandsChoices(),
                    'required'           => false,
                    'translation_domain' => 'LexikFormFilterBundle',
                ),
                'data_extraction_method' => function (Options $options) {
                    return $options['compound'] ? 'text' : 'default';
                },
        );
                
        if(version_compare(Kernel::VERSION, '3.1.0') < 0) {
            $defaults['choice_options']['choices_as_values'] = true; // must be removed for use in Symfony 3.1, needed for 2.8
        }
        
        $resolver
            ->setDefaults($defaults)
            ->setAllowedValues('data_extraction_method', array('text', 'default'))
            ->setAllowedValues('condition_operator', FilterOperands::getNumberOperands(true))
        ;
    }

    /**
     * @return ?string
     */
    public function getParent()
    {
        return NumberType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'filter_number';
    }
}
