<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ChoiceFilterType extends ChoiceType
{
    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return $options['expanded'] ? 'filter' : 'filter_field';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_choice';
    }
}