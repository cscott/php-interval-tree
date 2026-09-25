<?php

declare( strict_types=1 );

namespace Wikimedia\IntervalTree\Tests;

use PHPUnit\Framework\TestCase;
use Wikimedia\IntervalTree\Interval\NumericInterval;
use Wikimedia\IntervalTree\IntervalTree;
use Wikimedia\IntervalTree\Pair;

/**
 * @covers \Wikimedia\IntervalTree\IntervalTree
 * @uses \Wikimedia\IntervalTree\Interval\NumericInterval
 * @uses \Wikimedia\IntervalTree\Node
 * @uses \Wikimedia\IntervalTree\NodeColor
 * @uses \Wikimedia\IntervalTree\Pair
 */
final class IntervalTreeIterationTest extends TestCase {

	public function testEmptyTree(): void {
		$tree = new IntervalTree();
		$key = new NumericInterval( 1, 2 );
		self::assertSame( [], iterator_to_array( $tree->iterateFrom() ) );
		self::assertSame( [], iterator_to_array( $tree->iterateFrom( $key ) ) );
		self::assertSame( [], iterator_to_array( $tree->iterateBefore() ) );
		self::assertSame( [], iterator_to_array( $tree->iterateBefore( $key ) ) );
	}

	public function testIterateFromAndBefore(): void {
		$tree = new IntervalTree();
		foreach ( [ [ 5, 6 ], [ 1, 9 ], [ 3, 4 ], [ 3, 3 ], [ 7, 8 ] ] as [ $low, $high ] ) {
			$tree->insert( new NumericInterval( $low, $high ), "$low-$high" );
		}
		self::assertSame(
			[ '1-9', '3-3', '3-4', '5-6', '7-8' ],
			self::values( $tree->iterateFrom() )
		);
		self::assertSame(
			[ '3-3', '3-4', '5-6', '7-8' ],
			self::values( $tree->iterateFrom( new NumericInterval( 3, 3 ) ) )
		);
		self::assertSame(
			[ '3-4', '5-6', '7-8' ],
			self::values( $tree->iterateFrom( new NumericInterval( 3, 4 ) ) )
		);
		self::assertSame( [], self::values( $tree->iterateFrom( new NumericInterval( 8, 8 ) ) ) );
		self::assertSame(
			[ '7-8', '5-6', '3-4', '3-3', '1-9' ],
			self::values( $tree->iterateBefore() )
		);
		self::assertSame(
			[ '3-4', '3-3', '1-9' ],
			self::values( $tree->iterateBefore( new NumericInterval( 5, 6 ) ) )
		);
		self::assertSame( [], self::values( $tree->iterateBefore( new NumericInterval( 1, 9 ) ) ) );
	}

	/**
	 * @return array<string, array{int}>
	 */
	public static function provideSeeds(): array {
		$seeds = [];
		for ( $seed = 1; $seed <= 10; $seed++ ) {
			$seeds["seed $seed"] = [ $seed ];
		}
		return $seeds;
	}

	/**
	 * @dataProvider provideSeeds
	 */
	public function testRandomMatchesBruteForce( int $seed ): void {
		mt_srand( $seed );
		$tree = new IntervalTree();
		/** @var array<int, array{int, int}> $live */
		$live = [];
		for ( $op = 1; $op <= 200; $op++ ) {
			// Small domain, so there are many duplicate intervals.
			if ( count( $live ) < 2 || mt_rand( 0, 2 ) > 0 ) {
				$low = mt_rand( 0, 15 );
				$high = $low + mt_rand( 0, 3 );
				$tree->insert( new NumericInterval( $low, $high ), $op );
				$live[$op] = [ $low, $high ];
			} else {
				$value = array_rand( $live );
				self::assertTrue( $tree->remove( NumericInterval::fromArray( $live[$value] ), $value ) );
				unset( $live[$value] );
			}

			$keyLow = mt_rand( -1, 20 );
			$key = new NumericInterval( $keyLow, $keyLow + mt_rand( 0, 3 ) );
			$message = "seed $seed, operation $op";
			$sorted = self::sortedIntervals( $live );
			$notLess = array_values( array_filter(
				$sorted, static fn ( array $i ) => !NumericInterval::fromArray( $i )->lessThan( $key )
			) );
			$less = array_values( array_filter(
				$sorted, static fn ( array $i ) => NumericInterval::fromArray( $i )->lessThan( $key )
			) );

			self::assertSame( $sorted, self::intervals( $tree->iterateFrom() ), $message );
			self::assertSame( array_reverse( $sorted ), self::intervals( $tree->iterateBefore() ), $message );
			self::assertSame( $notLess, self::intervals( $tree->iterateFrom( $key ) ), $message );
			self::assertSame( array_reverse( $less ), self::intervals( $tree->iterateBefore( $key ) ), $message );

			// Every value is visited exactly once.
			$values = self::values( $tree->iterateFrom() );
			sort( $values );
			self::assertSame( array_keys( $live ), $values, $message );
		}
	}

	/**
	 * @param array<int, array{int, int}> $live
	 * @return list<array{int, int}>
	 */
	private static function sortedIntervals( array $live ): array {
		$sorted = array_values( $live );
		sort( $sorted );
		return $sorted;
	}

	/**
	 * @param iterable<Pair<int, mixed>> $pairs
	 * @return list<array{int, int}>
	 */
	private static function intervals( iterable $pairs ): array {
		$intervals = [];
		foreach ( $pairs as $pair ) {
			$intervals[] = [ $pair->getInterval()->getLow(), $pair->getInterval()->getHigh() ];
		}
		return $intervals;
	}

	/**
	 * @param iterable<Pair<int, mixed>> $pairs
	 * @return list<mixed>
	 */
	private static function values( iterable $pairs ): array {
		$values = [];
		foreach ( $pairs as $pair ) {
			$values[] = $pair->getValue();
		}
		return $values;
	}
}
