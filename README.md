LexikFormFilterBundle
=====================

This Symfony bundle aims to provide classes to build some form types dedicated to filter an entity.
Once you created your form type you will be able to update a doctrine query builder conditions from a form type.

[![Build Status](https://travis-ci.org/lexik/LexikFormFilterBundle.png?branch=master)](https://travis-ci.org/lexik/LexikFormFilterBundle)
[![Latest Stable Version](https://poser.pugx.org/lexik/form-filter-bundle/v/stable.svg)](https://packagist.org/packages/lexik/form-filter-bundle)
[![CI Tests](https://github.com/lexik/LexikFormFilterBundle/actions/workflows/ci.yml/badge.svg)](https://github.com/lexik/LexikFormFilterBundle/actions/workflows/ci.yml)

The idea is:

1. Create a form type extending from `Symfony\Component\Form\AbstractType` as usual.
2. Add form fields by using provided filter types (e.g. use TextFilterType::class instead of a TextType::class type) (*).
3. Then call a service to build the query from the form instance and execute your query to get your result :).

(*): In fact you can use any type, but if you want to apply a filter by not using a XxxFilterType::class type you will have to create a custom listener class to apply the filter for this type.

Documentation
=============

This Symfony bundle is compatible with Symfony 4.3 or higher.

For Symfony 2.8/3.4 please use tags v5.*

For installation and how to use the bundle refer to [Resources/doc/index.md](Resources/doc/index.md)

1. [Installation](Resources/doc/installation.md)
2. [Configuration](Resources/doc/configuration.md)
3. [Provided form types](Resources/doc/provided-types.md)
4. [Example & inner workings](Resources/doc/basics.md)
    1. [Simple example](Resources/doc/basics.md#i-simple-example)
    2. [Inner workings](Resources/doc/basics.md#ii-inner-workings)
5. [Working with the filters](Resources/doc/working-with-the-bundle.md)
    1. [Customize condition operator](Resources/doc/working-with-the-bundle.md#i-customize-condition-operator)
    2. [Filter customization](Resources/doc/working-with-the-bundle.md#ii-filter-customization)
    3. [Working with entity associations and embeddeding filters](Resources/doc/working-with-the-bundle.md#iii-working-with-entity-associations-and-embeddeding-filters)
    4. [Doctrine embeddables](Resources/doc/working-with-the-bundle.md#iv-doctrine-embeddables-orm)
    5. [Create your own filter type](Resources/doc/working-with-the-bundle.md#v-create-your-own-filter-type)
    6. [Enable validation on your filter type](Resources/doc/working-with-the-bundle.md#vi-enable-filtertype-form-validation)
6. [The FilterTypeExtension](Resources/doc/filtertypeextension.md)
7. [Working with other bundles](Resources/doc/working-with-other-bundles.md)
    1. [KNP Paginator example](Resources/doc/working-with-other-bundles.md#i-knp-paginator-example)

Community Support
-----------------

Please consider [opening a question on StackOverflow](http://stackoverflow.com/questions/ask) using the [`lexikformfilterbundle` tag](http://stackoverflow.com/questions/tagged/lexikformfilterbundle),  it is the official support platform for this bundle.
  
Github Issues are dedicated to bug reports and feature requests.

Symfony 2.8 and 3.4
-------------------

Please use last tag v5.*

Credits
-------

* Lexik <dev@lexik.fr>
* [All contributors](https://github.com/lexik/LexikFormFilterBundle/graphs/contributors)

License
-------

This bundle is under the MIT license.  
For the whole copyright, see the [LICENSE](LICENSE) file distributed with this source code.
