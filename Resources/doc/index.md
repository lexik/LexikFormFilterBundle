1. Installation
2. Configuration
3. Provided form types
4. Working with the filters
    1. Simple example
    2. Inner workings
    3. Customize condition operator (and/or)
    4. Filter customization
    5. Working with entity associations and embeddeding filters
    6. Doctrine embeddables
    7. Create your own filter type
5. The FilterTypeExtension

1. Installation
===============

Add the bundle to your `composer.json` file:

```javascript
require: {
    // ...
    "lexik/form-filter-bundle": "~2.0" // check packagist.org for more tags
    // ...
}
```

Then run a composer update:

```shell
composer.phar update
# OR
composer.phar update lexik/form-filter-bundle # to only update the bundle
```

Register the bundle with your kernel:

```php
    // in AppKernel::registerBundles()
    $bundles = array(
        // ...
        new Lexik\Bundle\FormFilterBundle\LexikFormFilterBundle(),
        // ...
    );
```

2. Configuration
================

Twig
----

You only need to add the following lines in your `app/config/config.yml`. This file contains the template blocks for the filter_xxx types.

```yaml
# app/config/config.yml
twig:
    form:
        resources:
            - LexikFormFilterBundle:Form:form_div_layout.html.twig
```

Bundle's options
----------------

* Case insensitivity:

If your RDBMS is Postgres, case insensitivity will be forced for LIKE comparisons.
If you want to avoid that, there is a configuration option:

```yaml
# app/config/config.yml
lexik_form_filter:
    force_case_insensitivity: false
```

If you use Postgres and you want your LIKE comparisons to be case sensitive
anyway, set it to `true`.

* Query builder method:

This option will define which method to use on the (doctrine) query builder to add the **entire** filter condition (not the operator between each condition).
By default this option is `null` so the bundle will call the `where()`method to set the entire filter condition.
So it will override the existing where clause. If you don't want the bundle override the where clause you can use the following option:

```yaml
# app/config/config.yml
lexik_form_filter:
    where_method: ~  # null | and | or
```

So if you set this option to `and` or `or` the bundle will use `andWhere()` or  `orWhere()`.

3. Provided types
=================

The bundle provides form types dedicated to filtering. Here the list of these types with their parent type and their specific options.

Notes: by default the `required` option is set to `false` for all filter_xxx types.

---
**filter_boolean:**

Parent type: _boolean_

---
**filter_checkbox:**

Parent type: _checkbox_

---
**filter_choice:**

Parent type: _choice_

---
**filter_date:**

Parent type: _date_

---
**filter_date_range:**

This type is composed of two filter_date types (left_date and right_date).

Parent type: _form_

Options:

* `left_date_options`: options to pass to the left filter_date type.
* `right_date_options`: options to pass to the right filter_date type.

---
**filter_datetime:**

Parent type: _datetime_

---
**filter_datetime_range:**

This type is composed of two filter_datetime types (left_datetime and right_datetime).

Parent type: _form_

Options:

* `left_datetime_options`: options to pass to the left filter_datetime type.
* `right_datetime_options`: options to pass to the right filter_datetime type.

---
**filter_entity:**

Parent type: _entity_

**This type does not support many-to-many relations.**

---
**filter_number:**

Parent type: _number_

Options:

* `condition_operator`: this option allows you to configure the operator you want to use, the default operator is FilterOperands::OPERATOR_EQUAL. See the FilterOperands::OPERATOR_xxx constants for all available operators.
You can also use FilterOperands::OPERAND_SELECTOR, this will display a combo box with the available operators in addition to the input text.

---
**filter_number_range:**

This type is composed of two filter_number types (left_number and right_number).

Parent type: _form_

Options:

* `left_number_options`: options to pass to the left filter_number type.
* `right_number_options`: options to pass to the right filter_number type.

---
**filter_text:**

Parent type: _text_

Options:

* `condition_pattern`: this option allows you to configure the way you to filter the string. The default pattern is FilterOperands::STRING_STARTS. See the FilterOperands::STRING_xxx constants for all available patterns.
You can also use FilterOperands::OPERAND_SELECTOR, this will display a combo box with available patterns in addition to the input text.


1. Working with the bundle
==========================

i. Simple example
-----------------

Here an example of how to use the bundle. Let's use the following entity:

```php
<?php
// MyEntity.php
namespace Project\Bundle\SuperBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class MyEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="integer")
     */
    protected $rank;
}
```

Create a type extended from AbstractType, add `name` and `rank` and use the filter_xxxx types.

```php
<?php
// ItemFilterType.php
namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ItemFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'filter_text');
        $builder->add('rank', 'filter_number');
    }

    public function getName()
    {
        return 'item_filter';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => false,
            'validation_groups' => array('filtering') // avoid NotBlank() constraint-related message
        ));
    }
}
```

Then in an action, create a form object from the ItemFilterType. Let's say we filter when the form is submitted with a GET method.

```php
<?php
// DefaultController.php
namespace Project\Bundle\SuperBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Project\Bundle\SuperBundle\Filter\ItemFilterType;

class DefaultController extends Controller
{
    public function testFilterAction()
    {
        $form = $this->get('form.factory')->create(new ItemFilterType());

        if ($this->get('request')->query->has($form->getName())) {
            // manually bind values from the request
            $form->submit($this->get('request')->query->get($form->getName()));

            // initialize a query builder
            $filterBuilder = $this->get('doctrine.orm.entity_manager')
                ->getRepository('ProjectSuperBundle:MyEntity')
                ->createQueryBuilder('e');

            // build the query from the given form object
            $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $filterBuilder);

            // now look at the DQL =)
            var_dump($filterBuilder->getDql());
        }

        return $this->render('ProjectSuperBundle:Default:testFilter.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
```

Basic template

```html
<!-- testFilter.html.twig -->
<form method="get" action=".">
    {{ form_rest(form) }}
    <input type="submit" name="submit-filter" value="filter" />
</form>
```

ii. Inner workings
------------------

A filter is applied by using events. Basically the `lexik_form_filter.query_builder_updater` service will trigger a default event named according to the form type to get the condition for a given filter.
Then once all condition have been gotten another event will be triggered to add these conditions to the (doctrine) query builder.
We provide a event/listener that supports Doctrine ORM and DBAL.

The default event name pattern is `lexik_form_filter.apply.<query_builder_type>.<form_type_name>`.

For example, let's say I use a form type with a name field:

```php
public function buildForm(FormBuilder $builder, array $options)
{
    $builder->add('name', 'filter_text');
}
```

The event name that will be triggered to get conditions to apply will be:

* `lexik_form_filter.apply.orm.filter_text` if you provide a `Doctrine\ORM\QueryBuilder`

* `lexik_form_filter.apply.dbal.filter_text` if you provide a `Doctrine\DBAL\Query\QueryBuilder`

Then another event will be triggered to add all the conditions to the (doctrine) query builder instance:

* `lexik_filter.apply_filters.orm` if you provide a `Doctrine\ORM\QueryBuilder`

* `lexik_filter.apply_filters.dbal` if you provide a `Doctrine\DBAL\Query\QueryBuilder`

iii. Customize condition operator
---------------------------------

By default the `lexik_form_filter.query_builder_updater` service will add conditions by using AND.
But you can customize the operator (and/or) to use between conditions when its added to the (doctrine) query builder.
To do so you will have to use the `filter_condition_builder` option in your main type class.

Here a simple example, the main type `ItemFilterType` is composed of 2 simple fileds and a sub type (RelatedOptionsType).
The `filter_condition_builder` option is expected to be a closuse that will be used to set operators to use between conditions.

```php
<?php

namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class RelatedOptionsType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('label', 'filter_text');
        $builder->add('rank', 'filter_number');
    }

    public function getName()
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

use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionBuilderInterface;

class ItemFilterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name', 'filter_text');
        $builder->add('date', 'filter_date');
        $builder->add('options', new RelatedOptionsType());
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
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

    public function getName()
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

iv. Filter customization
-------------------------


#### A. With the `apply_filter` option:

All filter types have an `apply_filter` option which is a closure.
If this option is defined the `QueryBuilderUpdater` won't trigger any event, but if will call the given closure instead.

The closure takes 3 parameters:

* an object that implements `Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface` from which you can get the query builder and the expression class.
* the field name.
* an array of values containing the field value and some other data.

```php
<?php
// ItemFilterType.php
namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Doctrine\ORM\QueryBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;

class ItemFilterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name', 'filter_text', array(
            'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                if (empty($values['value']) {
                    return null;
                }

                $paramName = sprintf('p_%s', str_replace('.', '_', $field));

                // expression that represent the condition
                $expression = $filterQuery->getExpr()->eq($field, ':'.$paramName);

                // expression parameters
                $parameters = array($paramName => $values['value']); / [ name => value ]
                // or if you need to define the parameter's type
                // $parameters = array($paramName => array($values['value'], \PDO::PARAM_STR)); // [ name => [value, type] ]

                return $filterQuery->createCondition($expression, $parameters);
            },
        ));
    }

    public function getName()
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
use Doctrine\ORM\QueryBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;

class ItemFilterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('position', 'filter_number');
    }

    public function getName()
    {
        return 'item_filter';
    }
}
```

The custom event name will be:

`lexik_form_filter.apply.orm.item_filter.position`

The correspondig listener could looks like:

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

```xml
<service id="my_bundle.listener.get_item_position_filter" class="MyBundle\EventListener\ItemPositionFilterConditionListener">
    <tag name="kernel.event_listener" event="lexik_form_filter.apply.orm.item_filter.position" method="onGetFilterCondition" />
</service>
```

Note that before triggering the default event name, the `lexik_form_filter.query_builder_updater` service checks if this custom event has some listeners, in which case this event will be triggered instead of the default one.


v. Working with entity associations and embeddeding filters
-----------------------------------------------------------

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

use Doctrine\ORM\QueryBuilder;

/**
 * Embed filter type.
 */
class OptionsFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('label', 'filter_text');
        $builder->add('color', 'filter_text');
    }

    public function getName()
    {
        return 'options_filter';
    }
}
```

Then we can use it in our `ItemFilterType` type. But we will embed it by using a `filter_collection_adapter` type.
This type will allow us to use the `add_shared` option to add joins (or other stuff) we needed to apply conditions on fields from the embedded type (`OptionsFilterType` here).

```php
<?php

namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuterInterface;

class ItemFilterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name', 'filter_text');
        $builder->add('rank', 'filter_number');

        $builder->add('options', 'filter_collection_adapter', array(
            'type'      => new OptionsFilterType(),
            'add_shared' => function (FilterBuilderExecuterInterface $qbe)  {
                $closure = function(QueryBuilder $filterBuilder, $alias, $joinAlias, Expr $expr) {
                    // add the join clause to the doctrine query builder
                    // the where clause for the label and color fields will be added automatically with the right alias later by the Lexik\Filter\QueryBuilderUpdater
                    $filterBuilder->leftJoin($alias . '.options', $joinAlias);
                };

                // then use the query builder executor to define the join, the join's alias and things to do on the doctrine query builder.
                $qbe->addOnce($qbe->getAlias().'.options', 'opt', $closure);
            },
        ));
    }

    public function getName()
    {
        return 'item_filter';
    }
}
```

#### B. Single object

So let's say we need to filter some Option by their related Item's name.
We can create a `OptionsFilterType` type and add the item field which will be a `ItemFilterType` and not a `filter_entity` as we need to filder on field that belong to Item.

Let's start with the `ItemFilterType`, the only thing we have to do is to change the default parent type of our by using the `getParent()` method.
This will allow us to use the `add_shared` option as in the `filter_collection_adapter` type (by default this option is not available on a type).

```php
namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ItemFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'filter_text');
    }

    public function getParent()
    {
        return 'filter_sharedable'; // this allow us to use the "add_shared" option
    }

    public function getName()
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
                $closure = function(QueryBuilder $filterBuilder, $alias, $joinAlias, Expr $expr) {
                    $filterBuilder->leftJoin($alias . '.item', $joinAlias);
                };

                $qbe->addOnce($qbe->getAlias().'.item', 'i', $closure);
            }
        ));
    }

    public function getName()
    {
        return 'filter_options';
    }
}
```

vi. Doctrine embeddables
------------------------

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

use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\EmbeddedFilterTypeInterface;

class AddressFilterType extends AbstractType implements EmbeddedFilterTypeInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('street', 'filter_text');
        $builder->add('postalCode', 'filter_text');
        // ...
    }
}
```

vii. Create your own filter type
--------------------------------

Let's see that through a simple example, we suppose I want to create a `LocaleFilterType` class to filter fields which contain a locale as value.

A filter type is basicaly a standard form type and Symfony provide a LocaleType that display a combox of locales.
So we can start by creating a form type, with the `locale` type as parent. We will also define a default value for the `data_extraction_method`, this options will define how the `lexik_form_filter.query_builder_updater` service will get infos from the form before the filter is applied.

So the `LocaleFilterType` class would look like:

```php
namespace Super\Namespace\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LocaleFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_extraction_method' => 'default',
            ))
            ->setAllowedValues(array(
                'data_extraction_method' => array('default'),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'locale';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_locale';
    }
}
```

Then defined the `LocaleFilterType` as a service and don't forget to add the `form.type` tag:

```xml
<service id="something.type.filter_locale" class="Super\Namespace\Type\LocaleFilterType">
    <tag name="form.type" alias="filter_locale" />
</service>
```

Now we can use the `filter_locale` type, but no filter will be applied. To apply a filter we need to listen some event, so let's create a subscriber:

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
     * Apply a filter for a filter_locale type.
     *
     * This method should work whih both ORM and DBAL query builder.
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


5. The FilterTypeExtension
==========================

The bundle loads a custom type extension to add the `apply_filter`,  `data_extraction_method`, and `filter_condition_builder` options to **all form types**. These options are used when a filter condition is applied to the query builder.

##### The `apply_filter` option:

This option is set to `null` by default and aims to override the default way to apply the filter on the query builder. So you can use it if the default way to apply a filter does match to your needs.

You can pass a Closure or a valid callback to this option, here is a simple example:

```php
<?php

use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CallbackFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('my_text_field', 'filter_text', array(
            'apply_filter' => array($this, 'textFieldCallback'),
        ));

        $builder->add('my_number_field', 'filter_number', array(
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

    public function getName()
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
* text: used with filter_text and filter_number types if you choose to display the combo box of available patterns/operator, it has the data from the combo box and the text field.
* value_keys: used with filter_xxx_range type to get values form each form child.

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
public function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder->add('my_text_field', 'filter_text', array(
        'data_extraction_method' => 'rainbow',
    ));
}
```

##### The `filter_condition_builder` option:

This option is used to defined the operator (and/or) to use between each condition.
This option is expected to be closure and recieve one parameter which is an instance of `Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionBuilderInterface`.

See 4.iii section for examples.
