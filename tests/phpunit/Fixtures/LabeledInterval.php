<?php

declare( strict_types=1 );

namespace Wikimedia\IntervalTree\Tests\Fixtures;

use Wikimedia\IntervalTree\Interval\IntervalInterface;

/**
 * A custom interval type that exercises the IntervalInterface contract in
 * unusual ways:
 *
 * - it carries an extra field (a label) and has its own constructor;
 * - it's ordered by high point, descending, rather than by low point;
 * - intersect() depends on the class of the query: against another
 *   LabeledInterval, the labels must also match; against any other
 *   interval, only the (closed) ranges must overlap.
 *
 * @implements IntervalInterface<int>
 */
final class LabeledInterval implements IntervalInterface {

	public function __construct(
		private readonly int $low,
		private readonly int $high,
		public readonly string $label,
	) {
	}

	public function getLow(): int {
		return $this->low;
	}

	public function getHigh(): int {
		return $this->high;
	}

	/**
	 * Order by high point descending, then low point ascending, then label.
	 * @param IntervalInterface<mixed> $otherInterval
	 * @return bool
	 */
	public function lessThan( IntervalInterface $otherInterval ): bool {
		return $this->key() < self::keyOf( $otherInterval );
	}

	/**
	 * @param IntervalInterface<mixed> $otherInterval
	 * @return bool
	 */
	public function equalTo( IntervalInterface $otherInterval ): bool {
		return $this->key() === self::keyOf( $otherInterval );
	}

	/**
	 * @param IntervalInterface<mixed> $otherInterval
	 * @return bool
	 */
	public function intersect( IntervalInterface $otherInterval ): bool {
		$overlap = $this->low <= $otherInterval->getHigh() && $otherInterval->getLow() <= $this->high;
		if ( $otherInterval instanceof self ) {
			return $overlap && $this->label === $otherInterval->label;
		}
		return $overlap;
	}

	/**
	 * @return array{int, int, string}
	 */
	private function key(): array {
		return [ -$this->high, $this->low, $this->label ];
	}

	/**
	 * @param IntervalInterface<mixed> $interval
	 * @return array{int|float, mixed, string}
	 */
	private static function keyOf( IntervalInterface $interval ): array {
		return $interval instanceof self ? $interval->key() :
			[ -$interval->getHigh(), $interval->getLow(), '' ];
	}
}
