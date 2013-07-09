<?php

/*
 * This file is part of the Doctrine Dump Fixtures Bundle
 *
 * (c) Piotr Plenik <piotr.plenik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TeamLab\Bundle\DumpFixturesBundle;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

abstract class AbstractFixture extends \Doctrine\Common\DataFixtures\AbstractFixture
    implements FixtureInterface, OrderedFixtureInterface
{
    protected function loadObjects(ObjectManager $manager, $className)
    {
        foreach ($this->getData($manager) as $name => $data) {
            $entity = new $className();

            foreach ($data as $field => $value) {
                $setterMethod = 'set' . str_replace('_', '', ucwords($field));

                if(!is_array($value))
                {
                    $entity->$setterMethod($value);
                    continue;
                }

                if(!array_key_exists('type', $value))
                    throw new Exception('Something goes wrong. ');

                if($value['type'] === 'reference')
                {
                    $entity->$setterMethod($manager->merge($this->getReference($value['name'])));
                    continue;
                }

                if($value['type'] === 'DateTime')
                {
                    $entity->$setterMethod(new \DateTime($value['name']));
                    continue;
                }
            }

            $manager->persist($entity);
            $manager->flush();

            $this->addReference($name, $entity);
        }
    }
}
