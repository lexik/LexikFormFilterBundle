<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Form;

use Symfony\Component\Form\AbstractExtension;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type;

/**
 * Load all filter types.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class FilterExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    protected function loadTypes()
    {
        return array(
            new Type\BooleanFilterType(),
            new Type\CheckboxFilterType(),
            new Type\ChoiceFilterType(),
            new Type\DateFilterType(),
            new Type\DateRangeFilterType(),
            new Type\DateTimeFilterType(),
            new Type\DateTimeRangeFilterType(),
            new Type\NumberFilterType(),
            new Type\NumberRangeFilterType(),
            new Type\TextFilterType(),
            new Type\CollectionAdapterFilterType(),
            new Type\SharedableFilterType(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadTypeExtensions()
    {
        return array(
            new FilterTypeExtension(),
        );
    }
}
