
1. Installation
===============

Add the bundle to your `composer.json` file:

```javascript
require: {
    // ...
    "lexik/form-filter-bundle": "~5.0" // check packagist.org for more tags
    // ...
}
```

Or install directly through composer with:

```
composer.phar require lexik/form-filter-bundle ~5.0
# For latest version
composer.phar require lexik/form-filter-bundle dev-master
```

Then run a composer update:

```shell
composer.phar update
# OR
composer.phar update lexik/form-filter-bundle # to only update the bundle
```

Register the bundle with your kernel:

```php
    // in AppKernel::registerBundles()
    $bundles = array(
        // ...
        new Lexik\Bundle\FormFilterBundle\LexikFormFilterBundle(),
        // ...
    );
```

***

Next: [2. Configuration](configuration.md)
