<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension;

use Symfony\Component\Form\AbstractExtension;

use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

/**
 * Load all filter types.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class FilterExtension extends AbstractExtension
{
    protected function loadTypes()
    {
        return array(
            new Type\FieldFilterType(),
            new Type\FilterType(),
            new Type\BooleanFilterType(),
            new Type\CheckboxFilterType(),
            new Type\ChoiceFilterType(),
            new Type\DateFilterType(),
            new Type\NumberFilterType(),
            new Type\NumberRangeFilterType(),
            new Type\TextFilterType(),
        );
    }
}