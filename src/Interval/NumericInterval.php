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

use InvalidArgumentException;
use function count;

/**
 * @template TPoint of int|float
 * @implements IntervalInterface<TPoint>
 */
final class NumericInterval implements IntervalInterface {
	/**
	 * @var TPoint
	 */
	private $low;

	/**
	 * @var TPoint
	 */
	private $high;

	/**
	 * NumericInterval constructor
	 * @param TPoint $low
	 * @param TPoint $high
	 */
	public function __construct( $low, $high ) {
		if ( $low > $high ) {
			throw new InvalidArgumentException( 'Low interval cannot be greater than high' );
		}

		$this->low = $low;
		$this->high = $high;
	}

	/**
	 * @template TFromPoint of int|float
	 * @param TFromPoint[] $interval
	 * @return NumericInterval<TFromPoint>
	 */
	public static function fromArray( array $interval ): NumericInterval {
		if ( count( $interval ) !== 2 ) {
			throw new InvalidArgumentException( 'Wrong interval array' );
		}
		return new self( $interval[0], $interval[1] );
	}

	/**
	 * Phan doesn't match TPoint with the interface's through @implements.
	 * @suppress PhanParamSignatureMismatch
	 * @return TPoint
	 */
	public function getLow() {
		return $this->low;
	}

	/**
	 * Phan doesn't match TPoint with the interface's through @implements.
	 * @suppress PhanParamSignatureMismatch
	 * @return TPoint
	 */
	public function getHigh() {
		return $this->high;
	}

	/**
	 * @param IntervalInterface<mixed> $otherInterval
	 * @return bool
	 */
	public function equalTo( IntervalInterface $otherInterval ): bool {
		return $this->getLow() === $otherInterval->getLow() && $this->getHigh() === $otherInterval->getHigh();
	}

	/**
	 * @param IntervalInterface<mixed> $otherInterval
	 * @return bool
	 */
	public function lessThan( IntervalInterface $otherInterval ): bool {
		return $this->getLow() < $otherInterval->getLow() ||
			( $this->getLow() === $otherInterval->getLow() && $this->getHigh() < $otherInterval->getHigh() );
	}

	/**
	 * @param IntervalInterface<mixed> $otherInterval
	 * @return bool
	 */
	public function intersect( IntervalInterface $otherInterval ): bool {
		return !( $this->getHigh() < $otherInterval->getLow() || $otherInterval->getHigh() < $this->getLow() );
	}

	/**
	 * Phan doesn't see a method template used as a class template argument.
	 * @suppress PhanTemplateTypeNotUsedInFunctionReturn
	 * @template TOther of int|float
	 * @param IntervalInterface<TOther> $otherInterval
	 * @return IntervalInterface<TPoint|TOther>
	 */
	public function merge( IntervalInterface $otherInterval ): IntervalInterface {
		return new NumericInterval(
			min( $this->getLow(), $otherInterval->getLow() ),
			max( $this->getHigh(), $otherInterval->getHigh() )
		);
	}
}
