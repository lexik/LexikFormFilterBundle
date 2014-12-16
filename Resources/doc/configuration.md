
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

***

Next: [3. Provided form types](provided-types.md)