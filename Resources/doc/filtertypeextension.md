
5. The FilterTypeExtension
==========================

The bundle loads a custom type extension to add the `apply_filter`,  `data_extraction_method`, and `filter_condition_builder` options to **all form types**. These options are used when a filter condition is applied to the query builder.

##### The `apply_filter` option:

This option is set to `null` by default and aims to override the default way to apply the filter on the query builder. So you can use it if the default way to apply a filter does match to your needs.

You can pass a Closure or a valid callback to this option, here is a simple example:

```php
<?php

use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CallbackFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('my_text_field', Filters\TextFilterType::class, array(
            'apply_filter' => array($this, 'textFieldCallback'),
        ));

        $builder->add('my_number_field', Filters\NumberFilterType::class, array(
            'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                if (empty($values['value'])) {
                    return null;
                }

                $expr = $filterQuery->getExpr();

                $paramName = sprintf('p_%s', str_replace('.', '_', $field));

                return $filterQuery->createCondition(
                    $expr->eq($field, ':'.$paramName),    // expression
                    array($paramName => $values['value']) // parameters [ name => value ]
                );
            },
        ));
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }

    public function textFieldCallback(QueryInterface $filterQuery, $field, $values)
    {
        if (empty($values['value'])) {
            return null;
        }

        $expr = $filterQuery->getExpr();

        $paramName = sprintf('p_%s', str_replace('.', '_', $field));

        return $filterQuery->createCondition(
            $expr->eq($field, ':'.$paramName),    // expression
            array($paramName => array($values['value'], \PDO::PARAM_STR) // parameters [ name => [value, type] ]
        );
    }
}
```

##### The `data_extraction_method` option:

This option replaces the `translaformer_id` option. This option defines the way we extract some data from the form before the filter is applied.

Available extration methods:

* default: simply get the form data.
* text: used with `TextFilterType` and `NumberFilterType` types if you choose to display the combo box of available patterns/operator, it has the data from the combo box and the text field.
* value_keys: used with `NumberRangeFilterType`, `DateTimeRangeFilterType` and `DateRangeFilterType` types to get values form each form child.

Create a custom extraction method:

```php
<?php

namespace Super\Namespace;

use Symfony\Component\Form\FormInterface;
use Lexik\Bundle\FormFilterBundle\Filter\DataExtractor\Method\DataExtractionMethodInterface;

class RainbowExtractionMethod implements DataExtractionMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'rainbow';
    }

    /**
     * {@inheritdoc}
     */
    public function extract(FormInterface $form)
    {
        $values = array(
            'value' => $form->getData(), // The value used to filter, most of time the form value.
        );

        // add other stuff into $values

        return $values;
    }
}
```

Then define your class as a service with the `lexik_form_filter.data_extraction_method` tag:

```xml
<service id="my_project.data_extraction_method.rainbow" class="Super\Namespace\RainbowExtractionMethod">
    <tag name="lexik_form_filter.data_extraction_method" />
</service>
```

Now you can use your method:

```php
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

public function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder->add('my_text_field', Filters\TextFilterType::class, array(
        'data_extraction_method' => 'rainbow',
    ));
}
```

##### The `filter_condition_builder` option:

This option is used to defined the operator (and/or) to use between each condition.
This option is expected to be closure and recieve one parameter which is an instance of `Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionBuilderInterface`.

See [4.iii section](working-with-the-bundle.md#iii-customize-condition-operator) for examples.

***

Next: [7. Working with other bundles](working-with-other-bundles.md)
