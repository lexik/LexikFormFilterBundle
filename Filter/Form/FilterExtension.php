<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Form;

use Lexik\Bundle\FormFilterBundle\Filter\Form\Type;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\BooleanFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\CheckboxFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\ChoiceFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\CollectionAdapterFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateTimeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\DateTimeRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\NumberFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\NumberRangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\SharedableFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
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
    protected function loadTypes(): array
    {
        return [new BooleanFilterType(), new CheckboxFilterType(), new ChoiceFilterType(), new DateFilterType(), new DateRangeFilterType(), new DateTimeFilterType(), new DateTimeRangeFilterType(), new NumberFilterType(), new NumberRangeFilterType(), new TextFilterType(), new CollectionAdapterFilterType(), new SharedableFilterType()];
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
