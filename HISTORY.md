# Release History

## v3.0.0 (unreleased)

Breaking changes:
* Require PHP 8.1 or later.
* `exist()` and `remove()` compare values strictly (`===`).  Previously a
  falsy value such as `0`, `''` or `null` matched any value.
* `Node::intersect()`, `Node::notIntersectLeftSubtree()` and
  `Node::notIntersectRightSubtree()` take an `IntervalInterface` rather
  than a `Node`.

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
* Adopt the MediaWiki coding conventions and library layout.

## v2.1.0

* Last release of the upstream
  [dan-on/php-interval-tree](https://github.com/dan-on/php-interval-tree).
