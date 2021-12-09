<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Form;

use Lexik\Bundle\FormFilterBundle\Filter\Form\Type;
use Symfony\Component\Form\AbstractExtension;

/**
 * Load all filter types.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class FilterExtension extends AbstractExtension
{
    /**
     * @return array
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
    public function loadTypeExtensions(): array
    {
        return [
            new FilterTypeExtension(),
        ];
    }
}
