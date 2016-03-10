4. Example & inner working
==========================

i. Simple example
-----------------

Here an example of how to use the bundle (with doctrine ORM). Let's use the following entity:

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

Create a type extended from AbstractType, add `name` and `rank` and use the filter types.

```php
<?php
// ItemFilterType.php
namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

class ItemFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', Filters\TextFilterType::class);
        $builder->add('rank', Filters\NumberFilterType::class);
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
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
use Symfony\Component\HttpFoundation\Request;
use Project\Bundle\SuperBundle\Filter\ItemFilterType;

class DefaultController extends Controller
{
    public function testFilterAction(Request $request)
    {
        $form = $this->get('form.factory')->create(new ItemFilterType());

        if ($request->query->has($form->getName())) {
            // manually bind values from the request
            $form->submit($request->query->get($form->getName()));

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

Filters are applied by using events. Basically the `lexik_form_filter.query_builder_updater` service will trigger a default event named according to the form type to get the condition for a given filter.
Then once all conditions have been gotten another event will be triggered to add these conditions to the (doctrine) query builder according to the operators defined by the condition builder.
We provide a event/listener that supports Doctrine ORM, DBAL and MongoDB.

The default event name pattern is `lexik_form_filter.apply.<query_builder_type>.<form_type_name>`.

For example, let's say I use a form type with a name field:

```php
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;

public function buildForm(FormBuilder $builder, array $options)
{
    $builder->add('name', Filters\TextFilterType::class);
}
```

The event name that will be triggered to get conditions to apply will be:

* `lexik_form_filter.apply.orm.filter_text` if you provide a `Doctrine\ORM\QueryBuilder`

* `lexik_form_filter.apply.dbal.filter_text` if you provide a `Doctrine\DBAL\Query\QueryBuilder`

* `lexik_form_filter.apply.mongodb.filter_text` if you provide a `Doctrine\ODM\MongoDB\Query\Builder`

Then another event will be triggered to add all the conditions to the (doctrine) query builder instance:

* `lexik_filter.apply_filters.orm` if you provide a `Doctrine\ORM\QueryBuilder`

* `lexik_filter.apply_filters.dbal` if you provide a `Doctrine\DBAL\Query\QueryBuilder`

* `lexik_filter.apply_filters.mongodb` if you provide a `Doctrine\ODM\MongoDB\Query\Builder`

***

Next: [5. Working with the filters](working-with-the-bundle.md)
