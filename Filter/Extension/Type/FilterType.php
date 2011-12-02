<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Component\Form\Extension\Core\Type\FormType;

/**
 * Filter type.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class FilterType extends FormType
{
    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return 'filter_field';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter';
    }
}
