<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Filter to use with boolean values.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class BooleanFilterType extends AbstractType
{
    public const VALUE_YES = 'y';
    public const VALUE_NO = 'n';

    /**
     * @return ?string
     */
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'filter_boolean';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(['required' => false, 'choices' => ['boolean.yes' => self::VALUE_YES, 'boolean.no' => self::VALUE_NO], 'placeholder' => 'boolean.yes_or_no', 'translation_domain' => 'LexikFormFilterBundle', 'data_extraction_method' => 'default'])
            ->setAllowedValues('data_extraction_method', ['default'])
        ;
        
        if (version_compare(Kernel::VERSION, '3.1.0') < 0) {
            $resolver->setDefault('choices_as_values', true); // must be removed for use in Symfony 3.1, needed for 2.8
        }
    }
}
