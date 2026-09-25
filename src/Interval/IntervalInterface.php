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
