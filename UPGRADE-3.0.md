UPGRADE FROM 2.1 to 3.0
=======================

#### Update way to add a condition by using the `apply_filter` option.

Now the callable defined by this option have to return a condition object (or null).
You can create this object by using the new `createCondition()` method from the $query object passed to the callable.
The callable should not add the condition to the doctrine query builder itself.

Before:

```php
$builder->add('name', 'filter_text', array(
    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
        if (!empty($values['value'])) {
            $qb = $filterQuery->getQueryBuilder();
            $qb->andWhere($filterQuery->getExpr()->eq($field, $values['value']));
        }
    },
));
```

After:

```php
$builder->add('name', 'filter_text', array(
    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
        if (!empty($values['value']) {
            $paramName = sprintf('p_%s', str_replace('.', '_', $field));

            // expression
            $expression = $filterQuery->getExpr()->eq($field, ':'.$paramName);

            // parameters
            $parameters = array(
                $paramName => array($values['value'], \PDO::PARAM_STR), // name => [value, type]
                // OR
                // $paramName => $values['value'] // name => value
            );

            return $filterQuery->createCondition($expression, $parameters);
        }

        return null;
    },
));
```