<?php

use Symfony\CS\Fixer\Contrib\HeaderCommentFixer;

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(__DIR__)
;

$header = <<<EOF
This file is part of the jupeter/DoctrineDumpFixturesBundle package.

(c) Piotr Plenik <piotr.plenik[at]gmail[dot]com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

HeaderCommentFixer::setHeader($header);

return Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    ->fixers(['-psr0'])
    ->finder($finder)
;
