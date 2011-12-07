<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class FilterChoiceType extends ChoiceType// implements FilterTypeInterface
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

    /**
     * {@inheritdoc}
     */
    /*public function applyFilter(QueryBuilder $queryBuilder, $field, $values)
    {

    }*/
}