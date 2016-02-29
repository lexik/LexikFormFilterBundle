Overview
========

This Symfony2 bundle aims to provide classes to build some form types dedicated to filter an entity.
Once you created your form type you will be able to update a doctrine query builder conditions from a form type.

[![Build Status](https://travis-ci.org/lexik/LexikFormFilterBundle.png?branch=master)](https://travis-ci.org/lexik/LexikFormFilterBundle)
[![Latest Stable Version](https://poser.pugx.org/lexik/form-filter-bundle/v/stable.svg)](https://packagist.org/packages/lexik/form-filter-bundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/1dc9c6d5-369d-4940-84a2-f0941ae5d16c/mini.png)](https://insight.sensiolabs.com/projects/1dc9c6d5-369d-4940-84a2-f0941ae5d16c)

The idea is:

1. Create a form type extending from `Symfony\Component\Form\AbstractType` as usual.
2. Add form fields by using provided filter types (e.g. use TextFilterType::class instead of a TextType::class type) (*).
3. Then call a service to build the query from the form instance and execute your query to get your result :).

(*): In fact you can use any type, but if you want to apply a filter by not using a XxxFilterType::class type you will have to create a custom listener class to apply the filter for this type.

Documentation
=============

This `Symfony3.0` branch is compatible with Symfony 2.8/3.0 or higher.

For installation and how to use the bundle refer to [Resources/doc/index.md](Resources/doc/index.md)

Running the test suite
======================

    composer install
    bin/phpunit
