<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Form\Type;

use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuterInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Filter to used to dynamically add joins.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class SharedableFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // keep the closure as attribute to execute it later in the query builder updater
        $builder->setAttribute('add_shared', $options['add_shared']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'add_shared' => function (FilterBuilderExecuterInterface $qbe) {},
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'filter_sharedable';
    }
}
