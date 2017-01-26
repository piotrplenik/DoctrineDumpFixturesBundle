<?php

/*
 * This file is part of the Doctrine Dump Fixtures Bundle
 *
 * (c) Piotr Plenik <piotr.plenik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TeamLab\Bundle\FixturesBundle\Reflection;

use Doctrine\Common\Annotations\AnnotationReader;

class PropertyProvider
{
    protected $className;
    protected $reflection;
    protected $propertyName;

    public function __construct($className, $propertyName)
    {
        $this->className = $className;
        $this->propertyName = $propertyName;
        $this->reflection = new \ReflectionProperty($className, $propertyName);
    }

    public function valid()
    {
        return $this->getPropertyDumpAnnotation() !== null;
    }

    public function asArray()
    {
        $return = array();

        $annotation = $this->getPropertyManyToOneAnnotation();

        if ($annotation) {
            $return['targetEntity'] = $annotation->targetEntity;
        }

        return $return;
    }

    /**
     * @return Doctrine\Bundle\DoctrineFixturesBundle\Mapping\Column
     */
    protected function getPropertyDumpAnnotation()
    {
        $annotationReader = new AnnotationReader();

        return $annotationReader->getPropertyAnnotation($this->reflection,
            'TeamLab\Bundle\FixturesBundle\Mapping\Column');
    }

    /**
     * @return Doctrine\ORM\Mapping\ManyToOne
     */
    protected function getPropertyManyToOneAnnotation()
    {
        $annotationReader = new AnnotationReader();

        return $annotationReader->getPropertyAnnotation($this->reflection,
            'Doctrine\ORM\Mapping\ManyToOne');
    }
}
