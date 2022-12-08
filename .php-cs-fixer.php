<?php

return Ibexa\CodeStyle\PhpCsFixer\InternalConfigFactory::build()
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__ . '/src')
            ->in(__DIR__ . '/tests')
            ->files()->name('*.php')
    )
;
