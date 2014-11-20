DoctrineDumpFixturesBundle
==========================

Bundle for Symfony 2, with you can dump database data into fixtures.

Dump Fixtures are used to dump database data into fixtures file.

Setup and Configuration
 -----------------------

 Doctrine Dump fixtures for Symfony are maintained in the `DoctrineDumpFixturesBundle`_.
 The bundle uses external `Doctrine Data Fixtures`_ library.

 Follow these steps to install the bundle and the library in the Symfony
 Standard edition. Add the following to your ``composer.json`` file:

```json
 {
     "require": {
         "jupeter/doctrine-dump-fixtures-bundle": "dev-master"
     }
 }
```

 Update the vendor libraries:

```bash
$ php composer.phar update
```

 If everything worked, the ``DoctrineDumpFixturesBundle`` can now be found
 at ``vendor/jupeter/doctrine-dump-fixtures-bundle``.

```
``DoctrineDumpFixturesBundle`` installs
`Doctrine Fixtures Bundle` and `Doctrine Data Fixtures`_ library. The library can be found
at ``vendor/doctrine``.
```

 Finally, register the Bundle ``DoctrineDumpFixturesBundle`` in ``app/AppKernel.php``.

```php
// ...
public function registerBundles()
{
    $bundles = array(
        // ...
        new TeamLab\Bundle\FixturesBundle\DoctrineDumpFixturesBundle(),
        // ...
    );
    // ...
}
```

Dump existing data into fixtures
--------------------------------

{todo}
