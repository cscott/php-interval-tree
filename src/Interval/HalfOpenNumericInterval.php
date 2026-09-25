<?php

declare( strict_types=1 );

namespace Wikimedia\IntervalTree\Interval;

use InvalidArgumentException;
use function count;

/**
 * A half-open numeric interval [low, high): it includes low but not high,
 * so [1, 5) and [5, 8) don't intersect.
 *
 * An empty interval [p, p) stands for the point p: it intersects the
 * intervals that contain p, and other empty intervals at p.  So
 * `findIntersections( new HalfOpenNumericInterval( $p, $p ) )` finds the
 * intervals containing p, and an empty interval stored in a tree can still
 * be found.
 *
 * intersect() treats the interval it's given as half-open too, whatever
 * its class.
 *
 * This class is also an example of a custom IntervalInterface
 * implementation; see the README.
 *
 * @template TPoint of int|float
 * @implements IntervalInterface<TPoint>
 */
final class HalfOpenNumericInterval implements IntervalInterface {
	/**
	 * @var TPoint
	 */
	private $low;

	/**
	 * @var TPoint
	 */
	private $high;

	/**
	 * @param TPoint $low The first point in the interval
	 * @param TPoint $high The first point after the interval
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
	 * @return HalfOpenNumericInterval<TFromPoint>
	 */
	public static function fromArray( array $interval ): HalfOpenNumericInterval {
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
	 * Returns true if this interval is empty, that is, a point.
	 */
	public function isEmpty(): bool {
		return !( $this->low < $this->high );
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
		$otherLow = $otherInterval->getLow();
		$otherHigh = $otherInterval->getHigh();
		$otherEmpty = !( $otherLow < $otherHigh );
		if ( $this->isEmpty() && $otherEmpty ) {
			// Two points: the same point?
			return !( $this->low < $otherLow || $otherLow < $this->low );
		}
		if ( $this->isEmpty() ) {
			// Is this point in the other interval?
			return $otherLow <= $this->low && $this->low < $otherHigh;
		}
		if ( $otherEmpty ) {
			// Is the other point in this interval?
			return $this->low <= $otherLow && $otherLow < $this->high;
		}
		return $this->low < $otherHigh && $otherLow < $this->high;
	}
}
