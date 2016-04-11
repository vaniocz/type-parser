# Type Parser

[![Build Status](https://travis-ci.org/vaniocz/type-parser.svg?branch=master)](https://travis-ci.org/vaniocz/type-parser)
[![Coverage Status](https://coveralls.io/repos/github/vaniocz/type-parser/badge.svg?branch=master)](https://coveralls.io/github/vaniocz/type-parser?branch=master) ![PHP7](https://img.shields.io/badge/php-7-6B7EB9.svg)
[![License](https://poser.pugx.org/vanio/type-parser/license)](https://packagist.org/packages/vanio/type-parser)

Library for parsing type expressions and/or property types defined using var PHPDoc annotation almost as defined in PSR-5 specification draft, just a little bit more permissive. It also supports merging two (or more) types like `int|string` -> `scalar` or `string[]|int[]` -> `scalar[]`

PSR-5 ABNF: https://github.com/phpDocumentor/fig-standards/blob/master/proposed/phpdoc.md#user-content-abnf

# Example
```php
<?php
use Doctrine\Common\Cache\FilesystemCache;
use Vanio\TypeParser\CachingParser;
use Vanio\TypeParser\Tests\Fixtures\Foo;
use Vanio\TypeParser\TypeParser;

$typeParser = new CachingParser(new TypeParser, new FilesystemCache(__DIR__ . '/cache'));
$type = $typeParser->parsePropertyTypes(Foo::class);
$type['scalar']->type(); // /** @var int|string */ -> new CompoundType(Type::INTEGER, Type::STRING) -> new SimpleType(Type::SCALAR)
```
