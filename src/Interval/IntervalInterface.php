<?php

declare( strict_types=1 );

namespace Danon\IntervalTree\Interval;

/**
 * @template TPoint
 */
interface IntervalInterface {
	/**
	 * @param TPoint $low
	 * @param TPoint $high
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
	 * @param IntervalInterface<TPoint> $otherInterval
	 * @return bool
	 */
	public function equalTo( IntervalInterface $otherInterval ): bool;

	/**
	 * @param IntervalInterface<TPoint> $otherInterval
	 * @return bool
	 */
	public function lessThan( IntervalInterface $otherInterval ): bool;

	/**
	 * @param IntervalInterface<TPoint> $otherInterval
	 * @return bool
	 */
	public function intersect( IntervalInterface $otherInterval ): bool;

	/**
	 * @param IntervalInterface<TPoint> $otherInterval
	 * @return IntervalInterface<TPoint>
	 */
	public function merge( IntervalInterface $otherInterval ): IntervalInterface;
}
