DoctrineDumpFixturesBundle
==========================

Bundle for Symfony 3, with you can dump database data into fixtures.

Dump Fixtures are used to dump database data into fixtures file.

Setup and Configuration
 -----------------------

 Doctrine Dump fixtures for Symfony are maintained in the `DoctrineDumpFixturesBundle`_.
 The bundle uses external `Doctrine Data Fixtures`_ library.

 Follow these steps to install the bundle and the library in the Symfony
 Standard edition. Run command in your project diretory:

```bash
$ composer require jupeter/doctrine-dump-fixtures-bundle
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

Configuration
-------------

To dump entity data, you need setup annotation for entity:

```php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use TeamLab\Bundle\FixturesBundle\Mapping as Dump;  # use Annotation

/**
 * @ORM\Entity
 * @Dump\Entity # configure with entities should be dumped
  */
class Offers 
{
     // ...
     
     /**
      * @ORM\Column(type="string")
      * @Dump\Column # configure with columns should be dumped
      */
     private function $name;
     
     // ...
}

```

Dump existing data into fixtures
--------------------------------

To dump all data from database to Fixtures, run command:

```bash
$ ./bin/console doctrine:fixtures:dump 
```
