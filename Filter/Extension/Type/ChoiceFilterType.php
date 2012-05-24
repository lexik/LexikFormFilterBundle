<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Filter type for select list.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class ChoiceFilterType extends ChoiceType
{
    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return isset($options['expanded']) && $options['expanded'] ? 'filter' : 'filter_field';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_choice';
    }
}