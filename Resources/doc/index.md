1. Installation
2. Configuration
3. Provided form types
4. Working with the filters
    * Simple example
    * Inner workings
    * Filter customization
    * Working with entity associations and embeddeding filters
5. The FilterTypeExtension

1. Installation
===============

Add the bundle to your `composer.json` file:

```javascript
require: {
    // ...
    "lexik/form-filter-bundle": "v2.0.0" // check packagist.org for more tags
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

You only need to add the following lines in your `app/config/config.yml`. This file contains the template blocks for the filter_xxx types.

```yaml
# app/config/config.yml
twig:
    form:
        resources:
            - LexikFormFilterBundle:Form:form_div_layout.html.twig
```

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


4. Working with the bundle
==========================

Simple example
--------------

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
// MySuperFilterType.php
namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MySuperFilterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name', 'filter_text');
        $builder->add('rank', 'filter_number');
    }

    public function getName()
    {
        return 'my_super_filter';
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

Then in an action, create a form object from the MySuperFilterType. Let's say we filter when the form is submitted with a post method.

```php
<?php
// DefaultController.php
namespace Project\Bundle\SuperBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Project\Bundle\SuperBundle\Filter\MySuperFilterType;

class DefaultController extends Controller
{
    public function testFilterAction()
    {
        $form = $this->get('form.factory')->create(new MySuperFilterType());

        if ($this->get('request')->query->has('submit-filter')) {
            // bind values from the request
            $form->bindRequest($this->get('request'));

            // initliaze a query builder
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

Inner workings
--------------

A filter is applied using events. Basically the `lexik_form_filter.query_builder_updater` service will trigger a default event named according to the form type, then a listner will apply the filter. We provide a subcriber that supports Doctrine ORM and DBAL.

The default event name pattern is `lexik_form_filter.apply.<query_builder_type>.<form_type_name>`.

For example, let's say I use a form type with a name field:

```php
public function buildForm(FormBuilder $builder, array $options)
{
    $builder->add('name', 'filter_text');
}
```

The event name that will be triggerered will be:

* `lexik_form_filter.apply.orm.filter_text` in the case you provide a `Doctrine\ORM\QueryBuilder`

* `lexik_form_filter.apply.dbal.filter_text` in the case you provide a `Doctrine\DBAL\Query\QueryBuilder`


Filter customization
--------------------


#### A. With the `apply_filter` option:

All filter types have an `apply_filter` option which is a closure.
If this option is defined the `QueryBuilderUpdater` won't trigger any event,  but instead will call the given closure.

The closure takes 3 parameters:

* an object that implements `Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface` from which you can get the query builder and the expression class.
* the expression class
* the field name
* an array of values containing the field value and some other data

```php
<?php
// MySuperFilterType.php
namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\QueryBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;

class MySuperFilterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name', 'filter_text', array(
            'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
            
                // add conditions you need :)
                
            },
        ));
    }

    public function getName()
    {
        return 'my_super_filter';
    }
}
```

#### B. By listening an event

Aonther way to override the default way to apply the filter is to listen a custom event name composed of the form type name plus the form type's parent names, so the custom event name is like:

`lexik_form_filter.apply.<query_builder_type>.<parents_field_name>.<field_name>`

For example, if I use the following form type:

```php
<?php
// MySuperFilterType.php
namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\QueryBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;

class MySuperFilterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('position', 'filter_number');
    }

    public function getName()
    {
        return 'my_super_filter';
    }
}
```

The custom event name will be:

`lexik_form_filter.apply.orm.my_super_filter.position`

Before triggering the default event name, the `lexik_form_filter.query_builder_updater` service checks if this custom event has some listeners, in which case this event will be triggered instead of the default one.


Working with entity associations and embeddeding filters
--------------------------------------------------------

You can embed a filter inside another one. It could be a way to filter elements associated to the "root" one.
Let's say the entity we filter with the `MySuperFilterType` filter is related to some options, and an option has a 2 fields: label and color.
We can filter entities by their option's label and color by creating and using a `OptionsFilterType` inside `MySuperFilterType`:

```php
<?php

namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class MySuperFilterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name', 'filter_text');
        $builder->add('rank', 'filter_number');
        $builder->add('options', new OptionsFilterType());
    }

    public function getName()
    {
        return 'my_super_filter';
    }
}
```

The `OptionsFilterType` class is a standard form that has to implement `Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterTypeSharedableInterface`.
This interface defines an `addShared()` method used to add joins (or other stuff) needed to apply conditions on fields from the embeded type (OptionsFilterType here).

```php
<?php

namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Doctrine\ORM\QueryBuilder;

use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuterInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Expr;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterTypeSharedableInterface;

/**
 * Embbed filter type.
 */
class OptionsFilterType extends AbstractType implements FilterTypeSharedableInterface
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

    /**
     * This method aim to add all joins you need
     */
    public function addShared(FilterBuilderExecuterInterface $qbe)
    {
        $closure = function(QueryBuilder $filterBuilder, $alias, $joinAlias, Expr $expr) {
            // add the join clause to the doctrine query builder
            // the where clause for the label and color fields will be added automatically with the right alias later by the Lexik\Filter\QueryBuilderUpdater
            $filterBuilder->leftJoin($alias . '.options', 'opt');
        }
    
        // then use the query builder executor to define the join, the join's alias and things to do on the doctrine query builder.
        $qbe->addOnce($qbe->getAlias().'.options', 'opt', $closure);
    }
}
```

5. The FilterTypeExtension
==========================

The bundle loads a custom type extension to add the `apply_filter` and the `data_extraction_method` options to **all form types**. These options are used when a filter condition is applied to the query builder.

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
                if (!empty($values['value'])) {
                    $qb = $filterQuery->getQueryBuilder();
                    $qb->andWhere($filterQuery->getExpr()->eq($field, $values['value']));
                }
            },
        ));
    }

    public function getName()
    {
        return 'item_filter';
    }

    public function textFieldCallback(QueryInterface $filterQuery, $field, $values)
    {
        if (!empty($values['value'])) {
            $qb = $filterQuery->getQueryBuilder();
            $qb->andWhere($filterQuery->getExpr()->eq($field, $values['value']));
        }
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
