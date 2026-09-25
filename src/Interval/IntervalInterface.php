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
	 * Constructors are exempt from variance rules, but phan doesn't know
	 * that, so give it a type without the covariant template.
	 * @suppress PhanGenericConstructorTypes
	 * @param TPoint $low
	 * @param TPoint $high
	 * @phan-param mixed $low
	 * @phan-param mixed $high
	 */
	public function __construct( $low, $high );

	/**
	 * Phan doesn't see a method template used as a class template argument.
	 * @suppress PhanTemplateTypeNotUsedInFunctionReturn
	 * @template TFromPoint
	 * @param TFromPoint[] $interval
	 * @return IntervalInterface<TFromPoint>
	 */
	public static function fromArray( array $interval ): IntervalInterface;

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

	/**
	 * Phan doesn't see a method template used as a class template argument.
	 * @suppress PhanTemplateTypeNotUsedInFunctionReturn
	 * @template TOther
	 * @param IntervalInterface<TOther> $otherInterval
	 * @return IntervalInterface<TPoint|TOther>
	 */
	public function merge( IntervalInterface $otherInterval ): IntervalInterface;
}
