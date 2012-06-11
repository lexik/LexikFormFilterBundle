Installation
============

Update your `deps` and `deps.lock` files:

    // deps
    ...
    [LexikFormFilterBundle]
        git=https://github.com/lexik/LexikFormFilterBundle.git
        target=/bundles/Lexik/Bundle/FormFilterBundle

    // deps.lock
    ...
    LexikFormFilterBundle <commit>

Register the namespaces with the autoloader:

    // app/autoload.php
     $loader->registerNamespaces(array(
        // ...
        'Lexik' => __DIR__.'/../vendor/bundles',
        // ...
    ));

Register the bundle with your kernel:

    // in AppKernel::registerBundles()
    $bundles = array(
        // ...
        new Lexik\Bundle\FormFilterBundle\LexikFormFilterBundle(),
        // ...
    );


Usage
=====

Here an example of how to use the bundle.
Once the bundle is loaded in your app, add this in your `app/config.yml`

```yaml
# app/config.yml
twig:
    form:
        resources:
            - LexikFormFilterBundle:Form:form_div_layout.html.twig
```

Let's use the following entity:

```php
<?php
// MyEntity.php
namespace Project\Bundle\SuperBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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

        if ($this->get('request')->getMethod() == 'POST') {
            // bind values from the request
            $form->bindRequest($this->get('request'));

            // initliaze a query builder
            $queryBuilder = $this->get('doctrine.orm.entity_manager')
                ->getRepository('ProjectSuperBundle:MyEntity')
                ->createQueryBuilder('e');

            // build the query from the given form object
            $this->get('lexik_form_filter.query_builder')->buildQuery($form, $queryBuilder);

            // now look at the DQL =)
            var_dump($queryBuilder->getDql());
        }

        return $this->render('ProjectSuperBundle:Default:testFilter.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
```

Basic template

```
// testFilter.html.twig
<form method="post">
    {{ form_rest(form) }}
    <input type="submit" name="submit-filter" value="filter" />
</form>
```

Override default way to apply the filter
========================================

All filter types have an `apply_filter` option which is a closure.
If this option is define the `Lexik\Bundle\FormFilterBundle\Filter\QueryBuilder` class won't call the `applyFilter()` method from the type class but it will call the given closure.

The closure take 4 paramerters:

* the Doctrine query builder
* the alias of the current table
* the field name
* an array of values containing the field value and some other data

```php
<?php
// MySuperFilterType.php
namespace Project\Bundle\SuperBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\Expr;
use Doctrine\ORM\QueryBuilder;

class MySuperFilterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name', 'filter_text', array(
            'apply_filter' => function (QueryBuilder $queryBuilder, $alias, $field, $values) {
            
                // add conditions you need
                
            },
        ));
    }

    public function getName()
    {
        return 'my_super_filter';
    }
}
```