#Type Parser

[![Build Status](https://api.travis-ci.org/vaniocz/type-parser.svg?branch=master)](https://travis-ci.org/vaniocz/type-parser) [![Coverage Status](https://coveralls.io/repos/github/vaniocz/type-parser/badge.svg?branch=master)](https://coveralls.io/github/vaniocz/type-parser?branch=master) [![Latest Stable Version](https://poser.pugx.org/vanio/type-parser/v/stable)](https://packagist.org/packages/vanio/type-parser) [![Total Downloads](https://poser.pugx.org/vanio/type-parser/downloads)](https://packagist.org/packages/vanio/type-parser) [![Latest Unstable Version](https://poser.pugx.org/vanio/type-parser/v/unstable)](https://packagist.org/packages/vanio/type-parser) [![License](https://poser.pugx.org/vanio/type-parser/license)](https://packagist.org/packages/vanio/type-parser)


Library for parsing type expressions and/or property types defined using var PHPDoc annotation almost as defined in PSR-5 specification draft, just a little bit more permissive.

PSR-5 ABNF: https://github.com/phpDocumentor/fig-standards/blob/master/proposed/phpdoc.md#user-content-abnf

#Example
```php
<?php
use Doctrine\Common\Cache\FilesystemCache;
use Vanio\TypeParser\CachingParser;
use Vanio\TypeParser\Tests\Fixtures\Foo;
use Vanio\TypeParser\TypeContextFactory;
use Vanio\TypeParser\TypeParser;
use Vanio\TypeParser\TypeResolver;
use Vanio\TypeParser\UseStatementsParser;

$typeParser = new TypeParser(new TypeResolver, new TypeContextFactory(new UseStatementsParser));
$typeParser = new CachingParser($typeParser, new FilesystemCache(__DIR__ . '/cache'));
$type = $typeParser->parsePropertyTypes(Foo::class);
$type['scalar']->type(); // /** @var int | string */ -> scalar
```
