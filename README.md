# IntervalTree
[![Latest Stable Version](https://poser.pugx.org/wikimedia/interval-tree/v)](https://packagist.org/packages/wikimedia/interval-tree) [![License](https://poser.pugx.org/wikimedia/interval-tree/license)](https://packagist.org/packages/wikimedia/interval-tree) [![PHP Version Require](https://poser.pugx.org/wikimedia/interval-tree/require/php)](https://packagist.org/packages/wikimedia/interval-tree)

> **IntervalTree was created by [Daniil Akhmetov](https://github.com/dan-on)**
> and first published as
> [dan-on/php-interval-tree](https://github.com/dan-on/php-interval-tree).
> This package is a fork of his work: the design, the API and nearly all of
> the code are his.  Thank you, Daniil!

## Overview

Package **wikimedia/interval-tree** is an implementation of self balancing binary search tree data structure called Red-Black Tree.

Based on interval tree described in "Introduction to Algorithms 3rd Edition", published by Thomas H. Cormen, Charles E. Leiserson, Ronald L. Rivest, and Clifford Stein.

## Complexity

| Operation | Best, Average, Worst   |
|-----------|------------------------|
| Insertion | O(log(n))              |
| Search    | O(log(n))              |
| Remove    | O(log(n))              |
| Space     | O(n)                   |

## Installing via Composer

```
composer require wikimedia/interval-tree
```

## Usage

### Interval Tree

#### insert(IntervalInterface $interval, mixed $value): void
Insert new pair (interval + value) into interval tree
```php
use Wikimedia\IntervalTree\IntervalTree;

$tree = new IntervalTree();
$tree->insert(new NumericInterval(1, 10), 'val1');
$tree->insert(new NumericInterval(2, 5), 'val2');
$tree->insert(new NumericInterval(11, 12), 'val3');
```

#### findIntersections(IntervalInterface $interval): Iterator\<Pair>
Find pairs which intervals intersect with given interval
```php
$intersections = $tree->findIntersections(new NumericInterval(3, 5));
foreach($intersections as $pair) {
    $pair->getInterval()->getLow(); // 1, 2
    $pair->getInterval()->getHigh(); // 10, 5
    $pair->getValue(); // 'val1', 'val2'
}
```

#### hasIntersection(IntervalInterface $interval): bool
Returns true if interval has at least one intersection in tree
```php
$tree->hasIntersection(new NumericInterval(3, 5)); // true
```

#### countIntersections(IntervalInterface $interval): int
Count intersections given interval in tree
```php
$tree->countIntersections(new NumericInterval(3, 5)); // 2
```

#### remove(IntervalInterface $interval, $value): bool
Remove node from tree by interval and value
```php
$tree->remove(new NumericInterval(11, 12), 'val3'); // true
```

#### exist(IntervalInterface $interval, $value): bool
Returns true if interval and value exist in the tree
```php
$tree->exist(new NumericInterval(11, 12), 'val3'); // true
```

#### isEmpty(): bool
Returns true if tree is empty
```php
$tree->isEmpty(); // false
```

#### getSize(): int
Get number of items stored in the interval tree
```php
$tree->getSize(); // 3
```

### Intervals

There are numeric and DateTimeInterface-based interval types included.

#### Numeric interval

```php
use Wikimedia\IntervalTree\Interval\NumericInterval;

// Instantiate numeric interval from array
$numericInterval = NumericInterval::fromArray([1, 100]);

// Instantiate numeric interval with constructor
$numericInterval = new NumericInterval(1, 100);
```

#### DateTime interval
```php
use Wikimedia\IntervalTree\Interval\DateTimeInterval;

// Instantiate DateTime interval from array
$dateTimeInterval = DateTimeInterval::fromArray([
    new DateTimeImmutable('2021-01-01 00:00:00'),
    new DateTimeImmutable('2021-01-02 00:00:00'),
]);

// Instantiate DateTime interval with constructor
$dateTimeInterval = new DateTimeInterval(
    new DateTimeImmutable('2021-01-01 00:00:00'), 
    new DateTimeImmutable('2021-01-02 00:00:00')
);
```

## Running tests

```
composer install
composer test
```

`composer test` runs the unit tests together with the MediaWiki coding
standard checks (phpcs, phan) and static analysis (Psalm, PHPStan, PHPMD).
Benchmarks are run with `composer bench`.

## Credits and license

IntervalTree was written by **Daniil Akhmetov**
([@dan-on](https://github.com/dan-on)), who designed its API and wrote
the red-black tree, the interval types, the tests and the benchmarks in
[dan-on/php-interval-tree](https://github.com/dan-on/php-interval-tree).
If this library is useful to you, please consider giving his original
repository a star.

Copyright (c) 2021 Akhmetov Daniil.  Released under the MIT license; see
[LICENSE](LICENSE).  Later changes are contributed under the same license.

## History

This library is a fork of
[dan-on/php-interval-tree](https://github.com/dan-on/php-interval-tree)
by Daniil Akhmetov.  The fork fixes several correctness bugs in removal
and lookup, follows the
[MediaWiki coding conventions](https://www.mediawiki.org/wiki/Manual:Coding_conventions/PHP),
and renames the package to `wikimedia/interval-tree` and the namespace to
`Wikimedia\IntervalTree`.  See [HISTORY.md](HISTORY.md) for details.

Additional documentation about this library can be found on
[mediawiki.org](https://www.mediawiki.org/wiki/IntervalTree).
