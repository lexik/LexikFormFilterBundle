Overview
========

This Symfony2 bundle aims to provide classes to build a form filter and then build a doctrine query from this form filter.

[![Build Status](https://travis-ci.org/lexik/LexikFormFilterBundle.png?branch=master)](https://travis-ci.org/lexik/LexikFormFilterBundle)
![Project Status](http://stillmaintained.com/lexik/LexikFormFilterBundle.png)
[![Latest Stable Version](https://poser.pugx.org/lexik/form-filter-bundle/v/stable.svg)](https://packagist.org/packages/lexik/form-filter-bundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/1dc9c6d5-369d-4940-84a2-f0941ae5d16c/mini.png)](https://insight.sensiolabs.com/projects/1dc9c6d5-369d-4940-84a2-f0941ae5d16c)

The idea is:

1. Create a form type extending from `Symfony\Component\Form\AbstractType` as usual.
2. Add form fields by using provided filter types (e.g. use `filter_text` instead of `text` type) (*).
3. Then call a service to build the query from the form instance and execute your query to get your result :).

(*): In fact you can use any type, but if you want to apply a filter by not using a `filter_xxx` type you will have to create a custom class to apply the filter for this type.

Documentation
=============

The `master` branch is compatible with Symfony 2.1 or higher, if you are using Symfony 2.0.x use the `symfony2.0` branch.

For installation and how to use the bundle refer to [Resources/doc/index.md](https://github.com/lexik/LexikFormFilterBundle/blob/master/Resources/doc/index.md)

Running the test suite
======================

    composer install
    bin/phpunit
