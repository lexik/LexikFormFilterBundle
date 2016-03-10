5. Working with the bundle
==========================

i. Customize condition operator
-------------------------------

By default the `lexik_form_filter.query_builder_updater` service will add conditions by using AND.
But you can customize the operator (and/or) to use between each conditions when its added to the (doctrine) query builder.
To do so you will have to use the `filter_condition_builder` option in your main type class.

Here a simple example, the main type `ItemFilterType` is composed of 2 simple fields and a sub type (RelatedOptionsType).
The `filter_condition_builder` option is expected to be a closuse that will be used to set operators to use between conditions.

```php
<?php

namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

class RelatedOptionsType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('label', Filters\TextFilterType::class);
        $builder->add('rank', Filters\NumberFilterType::class);
    }

    public function getBlockPrefix()
    {
        return 'related_options';
    }
}
```

```php
<?php

namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\OptionsResolver\ConditionBuilderInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionBuilderInterface;

class ItemFilterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name', Filters\TextFilterType::class);
        $builder->add('date', Filters\NumberFilterType::class);
        $builder->add('options', new RelatedOptionsType());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'filter_condition_builder' => function (ConditionBuilderInterface $builder) {
                $builder
                    ->root('or')
                        ->field('options.label')
                        ->andX()
                            ->field('options.rank')
                            ->field('name')
                        ->end()
                        ->field('date')
                    ->end()
                ;
            }
        ));
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }
}
```

With the above condition builder the complete where clause pattern will be: `WHERE <options.label> OR <date> OR (<options.rank> AND <name>)`.

Here another example of condition builder:

```php
$resolver->setDefaults(array(
    'filter_condition_builder' => function (ConditionBuilderInterface $builder) {
        $builder
            ->root('and')
                ->orX()
                    ->field('options.label')
                    ->field('name')
                ->end()
                ->orX()
                    ->field('options.rank')
                    ->field('date')
                ->end()
            ->end()
        ;
    }
));
```

The generated where clause will be: `WHERE (<options.label> OR <name>) AND (<options.rank> OR <date>)`.

ii. Filter customization
------------------------

#### A. With the `apply_filter` option:

All filter types have an `apply_filter` option which is a closure.
If this option is defined the `QueryBuilderUpdater` won't trigger any event, but if will call the given closure instead.

The closure takes 3 parameters:

* an object that implements `Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface` from which you can get the query builder and the expression class.
* the field name.
* an array of values containing the field value and some other data.

**Doctrine ORM/DBAL:**

```php
<?php
// ItemFilterType.php
namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

class ItemFilterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name', Filters\TextFilterType::class, array(
            'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                if (empty($values['value'])) {
                    return null;
                }

                $paramName = sprintf('p_%s', str_replace('.', '_', $field));

                // expression that represent the condition
                $expression = $filterQuery->getExpr()->eq($field, ':'.$paramName);

                // expression parameters
                $parameters = array($paramName => $values['value']); // [ name => value ]
                // or if you need to define the parameter's type
                // $parameters = array($paramName => array($values['value'], \PDO::PARAM_STR)); // [ name => [value, type] ]

                return $filterQuery->createCondition($expression, $parameters);
            },
        ));
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }
}
```

**Doctrine MongoDB:**

```php
<?php
// ItemFilterType.php
namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

class ItemFilterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name', Filters\TextFilterType::class, array(
            'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                if (empty($values['value'])) {
                    return null;
                }

                // expression that represent the condition
                $expression = $filterQuery->getExpr()
                    ->field($field)
                    ->equals($values['value']);

                return $filterQuery->createCondition($expression);
            },
        ));
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }
}
```

#### B. By listening an event

Another way to override the default way to apply the filter is to listen a custom event name.
This event name is composed of the form type name plus the form type's parent names, so the custom event name is like:

`lexik_form_filter.apply.<query_builder_type>.<parents_field_name>.<field_name>`

For example, if I use the following form type:

```php
<?php
// ItemFilterType.php
namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

class ItemFilterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('position', Filters\NumberFilterType::class);
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }
}
```

The custom event name will be:

`lexik_form_filter.apply.orm.item_filter.position`

The corresponding listener could looks like:

**Doctrine ORM/DBAL:**

```php
namespace MyBundle\EventListener;

use Lexik\Bundle\FormFilterBundle\Event\GetFilterConditionEvent;

class ItemPositionFilterConditionListener
{
    public function onGetFilterCondition(GetFilterConditionEvent $event)
    {
        $expr   = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if (!empty($values['value'])) {
            // create a parameter name from the field
            $paramName = sprintf('p_%s', str_replace('.', '_', $field));

            // Set the condition on the given event
            $event->setCondition(
                $expr->eq($event->getField(), ':' . $paramName),
                array($paramName => $values['value'])
            );
        }
    }
}
```

**Doctrine MongoDB:**

```php
namespace MyBundle\EventListener;

use Lexik\Bundle\FormFilterBundle\Event\GetFilterConditionEvent;

class ItemPositionFilterConditionListener
{
    public function onGetFilterCondition(GetFilterConditionEvent $event)
    {
        $expr   = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if (!empty($values['value'])) {
            // Set the condition on the given event
            $event->setCondition(
                $expr->field($event->getField())->equals($values['value'])
            );
        }
    }
}
```

```xml
<service id="my_bundle.listener.get_item_position_filter" class="MyBundle\EventListener\ItemPositionFilterConditionListener">
    <tag name="kernel.event_listener" event="lexik_form_filter.apply.orm.item_filter.position" method="onGetFilterCondition" />
</service>
```

Note that before triggering the default event name, the `lexik_form_filter.query_builder_updater` service checks if this custom event has some listeners, in which case this event will be triggered instead of the default one.

#### C. Disable filtering for one field

For any reason if you need to don't apply some filter condition for a given field, you can set the `apply_filter` option to `false`.
This will make the bundle skip the field, so no condition will be added for this field.

```php
<?php
// ItemFilterType.php
namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

class ItemFilterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name', Filters\TextFilterType::class, array(
            'apply_filter' => false, // disable filter
        ));
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }
}
```

iii. Working with entity associations and embeddeding filters
-------------------------------------------------------------

You can embed a filter inside another one. It could be a way to filter elements associated to the "root" one.

In the two following sections (A and B), I suppose we have 2 entities Item and Options.
And Item has a collection of Option and and Option has one Item.

#### A. Collection

Let's say the entity we filter with the `ItemFilterType` filter is related to a collection of options, and an option has 2 fields: label and color.
We can filter entities by their option's label and color by creating and using a `OptionsFilterType` inside `ItemFilterType`:

The `OptionsFilterType` class is a standard form, and would looks like:

```php
<?php

namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

/**
 * Embed filter type.
 */
class OptionsFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('label', Filters\TextFilterType::class);
        $builder->add('color', Filters\TextFilterType::class);
    }

    public function getBlockPrefix()
    {
        return 'options_filter';
    }
}
```

Then we can use it in our `ItemFilterType` type. But we will embed it by using a `CollectionAdapterFilterType` type.
This type will allow us to use the `add_shared` option to add joins (or other stuff) we needed to apply conditions on fields from the embedded type (`OptionsFilterType` here).

**Doctrine ORM/DBAL:**

```php
<?php

namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuterInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

class ItemFilterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name', Filters\TextFilterType::class);
        $builder->add('rank', Filters\NumberFilterType::class);

        $builder->add('options', Filters\CollectionAdapterFilterType::class, array(
            'type'       => new OptionsFilterType(),
            'add_shared' => function (FilterBuilderExecuterInterface $qbe)  {
                $closure = function (QueryBuilder $filterBuilder, $alias, $joinAlias, Expr $expr) {
                    // add the join clause to the doctrine query builder
                    // the where clause for the label and color fields will be added automatically with the right alias later by the Lexik\Filter\QueryBuilderUpdater
                    $filterBuilder->leftJoin($alias . '.options', $joinAlias);
                };

                // then use the query builder executor to define the join and its alias.
                $qbe->addOnce($qbe->getAlias().'.options', 'opt', $closure);
            },
        ));
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }
}
```

**Doctrine MongoDB:**

```php
<?php

namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ODM\MongoDB\Query\Expr;
use Doctrine\ODM\MongoDB\Query\Builder;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuterInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

class ItemFilterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name', Filters\TextFilterType::class);
        $builder->add('rank', Filters\NumberFilterType::class);

        $builder->add('options', Filters\CollectionAdapterFilterType::class, array(
            'type'       => new OptionsFilterType(),
            'add_shared' => function (FilterBuilderExecuterInterface $qbe)  {
                $closure = function (Builder $filterBuilder, $alias, $joinAlias, Expr $expr) {
                    // Here you can manipulate the MongoDB query builder if needed.
                    // Documents don't manage joins as in SQL so maybe you won't need to do specific things here.
                    // ...
                };

                $qbe->addOnce('options', 'options', $closure);
                // OR just add the alias
                $qbe->addOnce('options', 'options');
            },
        ));
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }
}
```

#### B. Single object

So let's say we need to filter some Option by their related Item's name.
We can create a `OptionsFilterType` type and add the item field which will be a `ItemFilterType` and not a `EntityFilterType` as we need to filter on field that belong to Item.

Let's start with the `ItemFilterType`, the only thing we have to do is to change the default parent type of our by using the `getParent()` method.
This will allow us to use the `add_shared` option as in the `CollectionAdapterFilterType` type (by default this option is not available on a type).

```php
namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

class ItemFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', Filters\TextFilterType::class);
    }

    public function getParent()
    {
        return Filters\SharedableFilterType::class; // this allow us to use the "add_shared" option
    }

    public function getBlockPrefix()
    {
        return 'filter_item';
    }
}
```

Then we can use our `ItemFilterType` inside `OptionsFilterType` and add the joins we need through the `add_shared` option.

```php
namespace Project\Bundle\SuperBundle\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuterInterface;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class OptionsFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('item', new ItemFilterType(), array(
            'add_shared' => function (FilterBuilderExecuterInterface $qbe) {
                $closure = function (QueryBuilder $filterBuilder, $alias, $joinAlias, Expr $expr) {
                    $filterBuilder->leftJoin($alias . '.item', $joinAlias);
                };

                $qbe->addOnce($qbe->getAlias().'.item', 'i', $closure);
            }
        ));
    }

    public function getBlockPrefix()
    {
        return 'filter_options';
    }
}
```
#### C. Use existing join alias defined on the query builder (ORM).

So as explained above you can add some joins dynamically.
But in case you've already set some joins on the query builder and you want to use them, you can use the `setParts()` method from the `lexik_form_filter.query_builder_updater` service.
This method allow you to pre-set aliases to use for each relation (join).

```php
$form = /* your form filter instance */;

$queryBuilder = $container
    ->getDoctrine()
    ->getManager()
    ->getRepository('Entity\Namespace')
    ->createQueryBuilder('e');

$queryBuilder
    ->select('e, u, a')
    ->leftJoin('e.user', 'u')
    ->innerJoin('u.addresses', 'a');

$qbUpdater = $container->get('lexik_form_filter.query_builder_updater');

// set the joins
$qbUpdater->setParts(array(
    '__root__'    => 'e',
    'e.user'      => 'u',
    'u.addresses' => 'a',
));

// then add filter conditions
$qbUpdater->addFilterConditions($form, $queryBuilder);
```

iv. Doctrine embeddables (ORM)
------------------------------

Here an example about how to create embedded filter types with Doctrine2 embeddables objects.
In the following code we suppose we use entities defined in the [doctrine tutorial](http://doctrine-orm.readthedocs.org/en/latest/tutorials/embeddables.html).

The `UserFilterType` is a standard type and simply embeds the `AddressFilterType`.

```php
namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ...
        $builder->add('address', new AddressFilterType());
        // ...
    }
}
```
Then in the `AddressFilterType` we will have to implement the `EmbeddedFilterTypeInterface`.
This interface does not define any methods, it's just used by the `lexik_form_filter.query_builder_updater` service to differentiate it from an embedded type with relations.

```php
namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\EmbeddedFilterTypeInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

class AddressFilterType extends AbstractType implements EmbeddedFilterTypeInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('street', Filters\TextFilterType::class);
        $builder->add('postalCode', Filters\NumberFilterType::class);
        // ...
    }
}
```

v. Create your own filter type
------------------------------

Let's see that through a simple example, we suppose I want to create a `LocaleFilterType` class to filter fields which contain a locale as value.

A filter type is basicaly a standard form type and Symfony provide a LocaleType that display a combox of locales.
So we can start by creating a form type, with the `locale` type as parent. We will also define a default value for the `data_extraction_method`, this options will define how the `lexik_form_filter.query_builder_updater` service will get infos from the form before the filter is applied.

So the `LocaleFilterType` class would look like:

```php
namespace Super\Namespace\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;

class LocaleFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_extraction_method' => 'default',
            ))
            ->setAllowedValues('data_extraction_method', array('default'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return LocaleType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'filter_locale';
    }
}
```

Then defined the `LocaleFilterType` as a service and don't forget to add the `form.type` tag:

```xml
<service id="something.type.filter_locale" class="Super\Namespace\Type\LocaleFilterType">
    <tag name="form.type" />
</service>
```

Now we can use the `LocaleFilterType` type, but no filter will be applied. To apply a filter we need to listen some event, so let's create a subscriber:

```php
namespace Super\Namespace\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Lexik\Bundle\FormFilterBundle\Event\GetFilterConditionEvent;

class FilterSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            // if a Doctrine\ORM\QueryBuilder is passed to the lexik_form_filter.query_builder_updater service
            'lexik_form_filter.apply.orm.filter_locale' => array('filterLocale'),

            // if a Doctrine\DBAL\Query\QueryBuilder is passed to the lexik_form_filter.query_builder_updater service
            'lexik_form_filter.apply.dbal.filter_locale' => array('filterLocale'),
        );
    }

    /**
     * Apply a filter for a LocaleFilterType type.
     *
     * This method should work with both ORM and DBAL query builder.
     */
    public function filterLocale(GetFilterConditionEvent $event)
    {
        $expr   = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if ('' !== $values['value'] && null !== $values['value']) {
            $paramName = str_replace('.', '_', $event->getField());

            $event->setCondition(
                $expr->eq($event->getField(), ':'.$paramName),
                array($paramName => $values['value'])
            );
        }
    }
}
```

Don't forget to defined the subscriber as a service.

```xml
<service id="lexik_form_filter.doctrine_subscriber" class="Super\Namespace\Listener\FilterSubscriber">
    <tag name="kernel.event_subscriber" />
</service>
```

Now the `lexik_form_filter.query_builder_updater` service is able to add filter condition for a locale field.

__Tip__: As you can see the `LocaleFilterType` class is very simple, we use the `default` data extraction method and we don't add any additional field to the form builder, we only use the parent form. In this case we could only create the listener and listen to `lexik_form_filter.apply.xxx.locale` instead of `lexik_form_filter.apply.xxx.filter_locale` and use the provided `locale` type:

```php
[...]
class FilterSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'lexik_form_filter.apply.orm.locale' => array('filterLocale'),
            'lexik_form_filter.apply.dbal.locale' => array('filterLocale'),
        );
    }
    [...]
}
```

vi. Enable FilterType form validation
-------------------------------------

By default most `FilterForms` are submitted using `GET`, and are defined in class instead of via a formBuilder in the controller. When you injected the data in the `FilterForm` yourself via the `$form->submit($data)` method, all was fine. In order to let the `validator` service function properly, we need to tell the form it does use the `GET` method:

```php
public function configureOptions(OptionsResolver $resolver)
{
    $resolver->setDefaults(array(
        'error_bubbling' 	=> true,
		'csrf_protection'   => false,
		'validation_groups' => array('filtering'), // avoid NotBlank() constraint-related message
        'method'            => 'get',
    ));
}
```

In order to automatically validate your requests, you have to make use of Symfony its built-in `$form->handleRequest()` function. In your controller, you can create your forms in a different way:

```php
// Handle the filtering
$filterForm = $this->createForm(new OrderFilterType());

$filterForm->handleRequest($request);

if ($filterForm->isValid()) {
	$filterBuilder = $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $filterBuilder);
}
```
Now the Symfony `requestHandler` will take over and won't `addFilterConditions` to the builder in case the form isn't valid.

***

Next: [6. The FilterTypeExtension](filtertypeextension.md)
