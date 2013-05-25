<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Filter type for date field.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class DateFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver
            ->setDefaults(array(
                'required'               => false,
                'data_extraction_method' => 'default',
            ))
            ->setAllowedValues(array(
                'data_extraction_method' => array('default'),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'date';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_date';
    }
}
