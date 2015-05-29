6. Working with other bundles
=============================

i. KNP Paginator example
-----------------

[KNP Paginator](https://github.com/KnpLabs/KnpPaginatorBundle) example based on the [simple example](working-with-the-bundle.md#i-simple-example).

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
        // initialize a query builder
        $filterBuilder = $this->get('doctrine.orm.entity_manager')
            ->getRepository('ProjectSuperBundle:MyEntity')
            ->createQueryBuilder('e');
    
        $form = $this->get('form.factory')->create(new ItemFilterType());

        if ($request->query->has($form->getName())) {
            // manually bind values from the request
            $form->submit($request->query->get($form->getName()));

            // build the query from the given form object
            $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $filterBuilder);
        }

        $query = $filterBuilder->getQuery();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->get('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return $this->render('ProjectSuperBundle:Default:testFilter.html.twig', array(
            'form' => $form->createView(),
            'pagination' => $pagination
        ));
    }
}
```
