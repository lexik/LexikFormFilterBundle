
2. Configuration
================

Twig
----

You only need to add the following lines in your `app/config/config.yml`. This file contains the template blocks for the filter types.

```yaml
# app/config/config.yml
twig:
    form_themes:
        - LexikFormFilterBundle:Form:form_div_layout.html.twig
```

Bundle's options
----------------

* Enable listeners you need:

The bundle provides some listener to apply conditions on Doctrine ORM, DBAL and MongoDB query builders.
By default only Doctrine ORM listeners are enabled.

```yaml
# app/config/config.yml
lexik_form_filter:
    listeners:
        doctrine_orm: true
        doctrine_dbal: false
        doctrine_mongodb: false
```

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

**For Doctrine ORM and DBAL only.**
This option will define which method to use on the (doctrine) query builder to add the **entire** condition computed from the form type (this option is not about the operator between each filter condition).
By default this option is set to `and`, so the bundle will call the `andWhere()` method to set the entire condition on the doctrine query builder.
If you set it to `null` or `or`, the bundle will use the `where()` or `orWhere()` method to set the entire condition.
And so if the value is `null` it will override the existing where clause (in case of you initialized one on the query builder).

```yaml
# app/config/config.yml
lexik_form_filter:
    where_method: ~  # null | and | or
```

***

Next: [3. Provided form types](provided-types.md)
