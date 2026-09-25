<?php
/**
 * Copyright (c) 2021 Akhmetov Daniil
 *
 * Originally written by Daniil Akhmetov as part of
 * https://github.com/dan-on/php-interval-tree
 *
 * SPDX-License-Identifier: MIT
 * See the LICENSE file for the full license text.
 *
 * @file
 */

declare( strict_types=1 );

namespace Wikimedia\IntervalTree\Interval;

/**
 * An interval that can be stored in an IntervalTree.
 *
 * You can implement this interface to store your own kind of interval.
 * The tree relies on the following, and gives wrong results if an
 * implementation breaks any of it:
 *
 * - Points are compared with PHP's `<` and `>` operators, so they must be
 *   values those compare sensibly: ints, floats, DateTimeInterface
 *   objects, and so on.  getLow() must not be greater than getHigh().
 * - lessThan() is a strict weak ordering, and equalTo() is true exactly
 *   when neither interval is less than the other.  The ordering needn't
 *   be by low point.  findIntersections() returns results in this order.
 * - intersect() is only true if the closed ranges [getLow(), getHigh()]
 *   of the two intervals overlap.  The tree uses the closed ranges to
 *   skip subtrees, and only calls intersect() on the rest.  Within that
 *   limit, intersect() can use any semantics: half-open intervals,
 *   intervals that must also match in some other field, and so on.
 * - The tree calls intersect() on the stored interval, passing the
 *   interval being searched for, which may be of a different class.  So an
 *   implementation can give intersect() a different meaning for different
 *   kinds of query.
 * - Intervals are immutable: an interval must not change while it's in a
 *   tree.
 *
 * @template-covariant TPoint
 */
interface IntervalInterface {
	/**
	 * @return TPoint
	 */
	public function getLow();

	/**
	 * @return TPoint
	 */
	public function getHigh();

	/**
	 * @param IntervalInterface<mixed> $otherInterval
	 * @return bool
	 */
	public function equalTo( IntervalInterface $otherInterval ): bool;

	/**
	 * @param IntervalInterface<mixed> $otherInterval
	 * @return bool
	 */
	public function lessThan( IntervalInterface $otherInterval ): bool;

	/**
	 * @param IntervalInterface<mixed> $otherInterval
	 * @return bool
	 */
	public function intersect( IntervalInterface $otherInterval ): bool;
}
