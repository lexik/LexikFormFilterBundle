UPGRADE FROM 1.x to 2.0
=======================

#### All XxxFilter classes has been removed

All filter classes from namespace `Lexik\Bundle\FormFilterBundle\Filter\ORM\Filters` has been removed.
Filter condition are now applied by some listeners.

#### AbstractFilterType has been removed

If you created form types dedicated to filtering, now just extends `Symfony\Component\Form\AbstractType`.

#### Transformer are replaced by DataExtractor.

In version 1.x, the bundle used some "transformers" to get some data from the form type in order to apply the filter condition.
In version 2 it has been replaced by a data extrator service that use data extraction methods.

So in your custom types:

Before:
```php
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
    $resolver
        ->setDefaults(array(
            // ...
            'transformer_id' => 'lexik_form_filter.transformer.text',
            // ...
        ))
        ->setAllowedValues(array(
            'transformer_id'    => array('lexik_form_filter.transformer.text','lexik_form_filter.transformer.default'),
        ))
    ;
}
```

After:

```php
public function setDefaultOptions(OptionsResolverInterface $resolver)
{
    $resolver
        ->setDefaults(array(
            // ...
            'data_extraction_method' => 'text';
            },
            // ...
        ))
        ->setAllowedValues(array(
            'data_extraction_method' => array('text','default'),
        ))
    ;
}
```

#### Parameters passed to the apply_filter option changed

Before: 
```php
$builder->add('name', 'filter_text', array(
    'apply_filter' => function (QueryBuilder $qb, Expr $expr, $field, array $values) {
        // ...
    },
));
```

After:

You can still get the Expr object from the QueryInterface object.

```php
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;

//...

$builder->add('name', 'filter_text', array(
    'apply_filter' => function (QueryInterface $filterQuery, $field, array $values) {
        // ...
    },
));
```
