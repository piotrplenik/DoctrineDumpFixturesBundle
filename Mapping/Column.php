<?php

/*
 * This file is part of the Doctrine Dump Fixtures Bundle
 *
 * (c) Piotr Plenik <piotr.plenik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TeamLab\Bundle\FixturesBundle\Mapping;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Column implements Annotation
{
    /** @var string */
    public $name;
    /** @var int */
    public $sequence;
}
