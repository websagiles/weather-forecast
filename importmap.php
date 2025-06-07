<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    'cypress' => [
        'version' => '14.4.1',
    ],
    '@badeball/cypress-cucumber-preprocessor' => [
        'version' => '22.0.1',
    ],
    '@bahmutov/cypress-esbuild-preprocessor' => [
        'version' => '2.2.5',
    ],
    'esbuild' => [
        'version' => '0.25.5',
    ],
    '@cucumber/tag-expressions' => [
        'version' => '6.1.2',
    ],
    'base64-js' => [
        'version' => '1.5.1',
    ],
    '@cucumber/cucumber-expressions' => [
        'version' => '18.0.1',
    ],
    'uuid' => [
        'version' => '10.0.0',
    ],
    'seedrandom' => [
        'version' => '3.0.5',
    ],
    'error-stack-parser' => [
        'version' => '2.1.4',
    ],
    'source-map' => [
        'version' => '0.6.1',
    ],
    'debug' => [
        'version' => '4.4.1',
    ],
    'regexp-match-indices' => [
        'version' => '1.0.2',
    ],
    'stackframe' => [
        'version' => '1.3.4',
    ],
    'ms' => [
        'version' => '2.1.3',
    ],
    'regexp-tree' => [
        'version' => '0.1.11',
    ],
    'tom-select' => [
        'version' => '2.4.3',
    ],
    '@orchidjs/sifter' => [
        'version' => '1.1.0',
    ],
    '@orchidjs/unicode-variants' => [
        'version' => '1.1.2',
    ],
    'tom-select/dist/css/tom-select.default.min.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'tom-select/dist/css/tom-select.default.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
];
