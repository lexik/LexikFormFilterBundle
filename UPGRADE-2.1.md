UPGRADE FROM 2.0 to 2.1
=======================

#### Remove support of old way to apply filter condition

The query builder updater service does not dispatch anymore the `lexik_filter.get` event.
So now you have to use the `apply_filer` option from the form type or listen a specific event, see the `Filter customization` section in the documentation.

#### Update way to embed filter inside another one

The `FilterTypeSharedableInterface` has been removed. The logic added by `FilterTypeSharedableInterface::addShared()` method is now added by using the `add_shared` option.

The following example suppose an Item has a collection of Option (check the documentation if need a relation to a single object).

Before:

The embedded type had to implement `FilterTypeSharedableInterface`.

```php
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ItemFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'filter_text');
        $builder->add('rank', 'filter_number');
        $builder->add('options', new OptionsFilterType());
    }

    public function getName()
    {
        return 'item_filter';
    }
}
```

```php
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\QueryBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterTypeSharedableInterface;

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
     * This method aims to add all joins you need
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

After:

Now you can use some types to be able to use the `add_shared` option.
This option expect a closure which has the same parameters as the `FilterTypeSharedableInterface::addShared()` method.
And you have no specific things to do in `OptionsFilterType`.

```php
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;

class ItemFilterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name', 'filter_text');
        $builder->add('rank', 'filter_number');

        $builder->add('options', 'filter_collection_adapter', array(
            'type'      => new OptionsFilterType(),
            'add_shared => funciton (FilterBuilderExecuterInterface $qbe)  {
                $closure = function(QueryBuilder $filterBuilder, $alias, $joinAlias, Expr $expr) {
                    // add the join clause to the doctrine query builder
                    // the where clause for the label and color fields will be added automatically with the right alias later by the Lexik\Filter\QueryBuilderUpdater
                    $filterBuilder->leftJoin($alias . '.options', $joinAlias');
                }

                // then use the query builder executor to define the join, the join's alias and things to do on the doctrine query builder.
                $qbe->addOnce($qbe->getAlias().'.options', 'opt', $closure);
            },
        );
    }

    public function getName()
    {
        return 'item_filter';
    }
}
```

```php
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

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
