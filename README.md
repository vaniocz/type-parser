# TypeParser [![Build Status](https://api.travis-ci.org/vaniocz/TypeParser.svg?branch=master)](https://travis-ci.org/vaniocz/TypeParser)

Library for parsing type expressions and/or property types defined using var PHPDoc annotation almost as defined in PSR-5 specification draft, just a little bit more permissive.

PSR-5 ABNF: https://github.com/phpDocumentor/fig-standards/blob/master/proposed/phpdoc.md#user-content-abnf

Work in Progress
### TODO:
- Move some responsibility to `TypeResolver::resolve (\ReflectionProperty $reflectionProperty, string class = null)`
- Partial imports (`use A\B; @var B\C`)
- `scalar` pseudo-type, which is either a `string`, `integer`, `float` or `boolean`.
- Generic type parameters union and/or recursive/deep ones? (`array<int|string, array<int|string>>`) PSR-5 contains it in the spec draft, but does not look they have generics implemented 
- Array expressions `(int|string)[]`
- Smarter merging of collection generic compounds (`Collection<int, string>|Collection<string, string> -> Collection<scalar, string>` or `Collection<int, string>|Collection<string> -> Collection<string>`)
- Allow returning type compounds and then only optionally resolve them into a single type (like it's done now, just using polymorphism instead of the hardcoded logic inside the parser)
