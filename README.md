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

<!--
Every example in the Usage and Examples sections is mirrored in
tests/phpunit/ReadmeExamplesTest.php.  If you change an example here,
change the test to match, and vice versa.
-->

Intervals are closed: they include both endpoints, so `[1, 5]` and
`[5, 8]` intersect.  A tree can hold several values with the same
interval; `exist()` and `remove()` identify an entry by its interval
*and* its value, compared with `===`.

### Interval Tree

The examples in this section build on each other.

#### insert(IntervalInterface $interval, mixed $value): void
Insert new pair (interval + value) into interval tree
```php
use Wikimedia\IntervalTree\Interval\NumericInterval;
use Wikimedia\IntervalTree\IntervalTree;

$tree = new IntervalTree();
$tree->insert(new NumericInterval(1, 10), 'val1');
$tree->insert(new NumericInterval(2, 5), 'val2');
$tree->insert(new NumericInterval(11, 12), 'val3');
```

#### findIntersections(IntervalInterface $interval): Iterator\<Pair>
Find pairs which intervals intersect with given interval.  Pairs are
returned in order of their intervals.
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
$tree->hasIntersection(new NumericInterval(20, 30)); // false
```

#### countIntersections(IntervalInterface $interval): int
Count intersections given interval in tree
```php
$tree->countIntersections(new NumericInterval(3, 5)); // 2
```

#### exist(IntervalInterface $interval, $value): bool
Returns true if interval and value exist in the tree
```php
$tree->exist(new NumericInterval(11, 12), 'val3'); // true
$tree->exist(new NumericInterval(11, 12), 'val1'); // false
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

#### remove(IntervalInterface $interval, $value): bool
Remove node from tree by interval and value.  Returns false if there is
no such pair in the tree.
```php
$tree->remove(new NumericInterval(11, 12), 'val3'); // true
$tree->remove(new NumericInterval(11, 12), 'val3'); // false
$tree->getSize(); // 2
```

### Intervals

There are numeric and DateTimeInterface-based interval types included.
You can also store your own kind of interval by implementing
`IntervalInterface`; see [Custom interval types](#custom-interval-types).

#### Numeric interval

```php
use Wikimedia\IntervalTree\Interval\NumericInterval;

// Instantiate numeric interval from array
$numericInterval = NumericInterval::fromArray([1, 100]);

// Instantiate numeric interval with constructor
$numericInterval = new NumericInterval(1, 100);

// Floats work too
$numericInterval = new NumericInterval(0.5, 99.5);
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

Both constructors throw an `InvalidArgumentException` if the low end is
greater than the high end.

### Custom interval types

To store your own kind of interval, implement `IntervalInterface`:
`getLow()`, `getHigh()`, `lessThan()`, `equalTo()` and `intersect()`.
The tree relies on the following:

- Points are compared with `<` and `>`, so they must be values PHP compares
  sensibly (ints, floats, `DateTimeInterface` objects, ...), and `getLow()`
  must not be greater than `getHigh()`.
- `lessThan()` is a strict weak ordering, and `equalTo()` is true exactly
  when neither interval is less than the other.  The ordering needn't be by
  low point; `findIntersections()` returns results in this order.
- `intersect()` may only be true if the closed ranges `[getLow(), getHigh()]`
  of the two intervals overlap; the tree uses those ranges to skip
  subtrees.  Otherwise `intersect()` can mean whatever you need, for
  example half-open intervals, or intervals that must also match in some
  other field.
- `intersect()` is called on the stored interval with the query interval as
  its argument.  The query can be of a different class, so different kinds
  of query can have different meanings.
- Intervals must not change while they're in a tree.

## Examples

<!-- Mirrored in tests/phpunit/ReadmeExamplesTest.php; keep them in sync. -->

### Finding overlapping bookings

```php
use Wikimedia\IntervalTree\Interval\DateTimeInterval;
use Wikimedia\IntervalTree\IntervalTree;

$bookings = new IntervalTree();
$bookings->insert(new DateTimeInterval(
    new DateTimeImmutable('2026-10-01 09:00'),
    new DateTimeImmutable('2026-10-01 09:15')
), 'Standup');
$bookings->insert(new DateTimeInterval(
    new DateTimeImmutable('2026-10-01 11:00'),
    new DateTimeImmutable('2026-10-01 12:30')
), 'Design review');
$bookings->insert(new DateTimeInterval(
    new DateTimeImmutable('2026-10-01 12:00'),
    new DateTimeImmutable('2026-10-01 13:00')
), 'Lunch');

$proposed = new DateTimeInterval(
    new DateTimeImmutable('2026-10-01 12:15'),
    new DateTimeImmutable('2026-10-01 12:45')
);
$conflicts = [];
foreach ($bookings->findIntersections($proposed) as $pair) {
    $conflicts[] = $pair->getValue();
}
// $conflicts is ['Design review', 'Lunch']
```

### Annotations on a range of text

Store each annotation with the character offsets it covers, then find
the annotations that touch a selection.  Because intervals are closed,
annotations that end at the start of the selection, or start at its
end, are included.

```php
use Wikimedia\IntervalTree\Interval\NumericInterval;
use Wikimedia\IntervalTree\IntervalTree;

$annotations = new IntervalTree();
$annotations->insert(new NumericInterval(0, 4), ['type' => 'bold']);
$annotations->insert(new NumericInterval(3, 9), ['type' => 'link', 'href' => 'Main_Page']);
$annotations->insert(new NumericInterval(12, 20), ['type' => 'italic']);
$annotations->insert(new NumericInterval(25, 30), ['type' => 'bold']);

$types = [];
foreach ($annotations->findIntersections(new NumericInterval(4, 12)) as $pair) {
    $types[] = $pair->getValue()['type'];
}
// $types is ['bold', 'link', 'italic']
```

### Several values for the same interval

```php
use Wikimedia\IntervalTree\Interval\NumericInterval;
use Wikimedia\IntervalTree\IntervalTree;

$tree = new IntervalTree();
$tree->insert(new NumericInterval(1, 5), 'a');
$tree->insert(new NumericInterval(1, 5), 'b');
$tree->insert(new NumericInterval(1, 5), 'c');
$tree->countIntersections(new NumericInterval(2, 3)); // 3

// Values are compared strictly, so this doesn't match 'a', 'b' or 'c'
$tree->remove(new NumericInterval(1, 5), 0); // false

$tree->remove(new NumericInterval(1, 5), 'b'); // true
$tree->exist(new NumericInterval(1, 5), 'a'); // true
$tree->exist(new NumericInterval(1, 5), 'b'); // false
$tree->countIntersections(new NumericInterval(2, 3)); // 2
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
