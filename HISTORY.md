# Release History

## v3.0.0 (unreleased)

Breaking changes:
* Forked from `dan-on/php-interval-tree` and renamed: the package is now
  `wikimedia/interval-tree` and the namespace `Wikimedia\IntervalTree`
  (previously `Danon\IntervalTree`).
* Require PHP 8.1 or later.
* `exist()` and `remove()` compare values strictly (`===`).  Previously a
  falsy value such as `0`, `''` or `null` matched any value.
* `Node::intersect()`, `Node::notIntersectLeftSubtree()` and
  `Node::notIntersectRightSubtree()` take an `IntervalInterface` rather
  than a `Node`.
* `IntervalInterface` no longer declares `__construct()`, `fromArray()` or
  `merge()`, so that classes with other constructors can implement it.
  `NumericInterval` and `DateTimeInterval` still have all three, and their
  `fromArray()` now returns the concrete class.
* `IntervalInterface`'s point type is covariant, so its `equalTo()`,
  `lessThan()`, `intersect()` and `merge()` accept intervals of any point
  type in static analysis.  Implementations that declared a narrower
  generic type for these parameters must widen it.

Bug fixes:
* Fix red-black rebalancing after removal, which corrupted the tree and
  led to `TypeError`s and wrong query results.
* Fix the max augmentation after a right rotation, which made
  `findIntersections()` miss matching intervals.
* `exist()` and `remove()` now find an (interval, value) pair when other
  pairs with the same interval are in the tree.
* Querying an empty tree, or a tree emptied by `remove()`, no longer
  throws.
* Fix the PHP 8.4 deprecation warning for an implicitly nullable
  parameter.

Other changes:
* Document what a custom `IntervalInterface` implementation must do to be
  stored in a tree.
* Adopt the MediaWiki coding conventions and library layout.

## v2.1.0

* Last release of
  [dan-on/php-interval-tree](https://github.com/dan-on/php-interval-tree),
  from which this library was forked.
