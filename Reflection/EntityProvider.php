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
use TeamLab\Bundle\FixturesBundle\Mapping\Entity;

class EntityProvider
{
    protected $className;

    /**
     * @var \ReflectionClass
     */
    protected $reflection;

    public function __construct($className)
    {
        $this->className = $className;
        $this->reflection = new \ReflectionClass($className);
    }

    public function valid()
    {
        return ($this->getClassDumpAnnotation() !== null);
    }

    public function getName()
    {
        $annotation = $this->getClassDumpAnnotation();

        if(isset($annotation->name)) {
            return $annotation->name;
        }

        $annotation = $this->getClassORMAnnotation();

        if(isset($annotation->name)) {
            return $this->getClassORMAnnotation()->name;
        }

        return $this->getClassName();
    }

    public function getClassName()
    {
        return $this->reflection->getShortName();
    }

    public function getSequence()
    {
        $annotation = $this->getClassDumpAnnotation();

        if(isset($annotation->sequence)) {
            return $annotation->sequence;
        }

        return 0;
    }

    public function getDumpFields()
    {
        if(!$this->valid()) {
            return false;
        }

        $fields = array();

        foreach($this->reflection->getProperties() as $property) {
            $propertyClass = new PropertyProvider($this->className, $property->getName());
            if(!$propertyClass->valid()) {
                continue;
            }

            $fields[$property->getName()] = $propertyClass->asArray();
        }

        return $fields;
    }

    /**
     * @return Entity
     */
    protected function getClassDumpAnnotation()
    {
        $annotationReader = new AnnotationReader();
        return $annotationReader->getClassAnnotation($this->reflection,
            'TeamLab\Bundle\FixturesBundle\Mapping\Entity');
    }

    /**
     * @return mixed
     */
    protected function getClassORMAnnotation()
    {
        $annotationReader = new AnnotationReader();
        return $annotationReader->getClassAnnotation($this->reflection,
            'Doctrine\ORM\Mapping\Table');
    }

}