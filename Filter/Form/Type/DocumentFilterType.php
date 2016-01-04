<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Form\Type;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Filter type for MongoDB documents.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class DocumentFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('filter_options', array(
            'reference_type' => $options['reference_type'],
            'reference_name' => (null !== $options['reference_name']) ? $options['reference_name'] : ucfirst($builder->getName()),
        ));
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'required'               => false,
                'data_extraction_method' => 'default',
                'reference_type'         => 'one',
                'reference_name'         => null,
            ))
            ->setRequired(array('reference_type'))
            ->setAllowedValues('data_extraction_method', array('default'))
            ->setAllowedValues('reference_type', array('one', 'many'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return DocumentType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'filter_document';
    }
}
