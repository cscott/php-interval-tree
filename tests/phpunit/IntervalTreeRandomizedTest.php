<?php

declare( strict_types=1 );

namespace Wikimedia\IntervalTree\Tests;

use PHPUnit\Framework\TestCase;
use Wikimedia\IntervalTree\Interval\NumericInterval;
use Wikimedia\IntervalTree\IntervalTree;

/**
 * Compares the tree against a brute-force list under a random mix of
 * inserts and removals, first with distinct intervals (exercising
 * rebalancing and max-augmentation) and then with many duplicate intervals
 * (exercising lookup of equal intervals with different values).
 *
 * @covers \Wikimedia\IntervalTree\IntervalTree
 * @uses \Wikimedia\IntervalTree\Interval\NumericInterval
 * @uses \Wikimedia\IntervalTree\Node
 * @uses \Wikimedia\IntervalTree\NodeColor
 * @uses \Wikimedia\IntervalTree\Pair
 */
final class IntervalTreeRandomizedTest extends TestCase {
	/**
	 * @return array<string, array{int}>
	 */
	public static function provideSeeds(): array {
		$seeds = [];
		for ( $seed = 1; $seed <= 50; $seed++ ) {
			$seeds["seed $seed"] = [ $seed ];
		}
		return $seeds;
	}

	/**
	 * @dataProvider provideSeeds
	 */
	public function testRandomInsertRemoveMatchesBruteForce( int $seed ): void {
		mt_srand( $seed );
		/** @var IntervalTree<int, int> $tree */
		$tree = new IntervalTree();
		/** @var array<int, array{int, int}> $live */
		$live = [];
		for ( $op = 1; $op <= 300; $op++ ) {
			if ( count( $live ) < 2 || mt_rand( 0, 2 ) > 0 ) {
				$low = mt_rand( 0, 1000 ) * 1000 + $op;
				$high = $low + mt_rand( 0, 30000 );
				$tree->insert( new NumericInterval( $low, $high ), $op );
				$live[$op] = [ $low, $high ];
			} else {
				$value = array_rand( $live );
				[ $low, $high ] = $live[$value];
				self::assertTrue( $tree->remove( new NumericInterval( $low, $high ), $value ) );
				unset( $live[$value] );
			}

			$queryLow = mt_rand( 0, 1030000 );
			$queryHigh = $queryLow + mt_rand( 0, 20000 );
			$expected = [];
			foreach ( $live as $value => [ $low, $high ] ) {
				if ( !( $high < $queryLow || $queryHigh < $low ) ) {
					$expected[] = $value;
				}
			}
			$actual = [];
			foreach ( $tree->findIntersections( new NumericInterval( $queryLow, $queryHigh ) ) as $pair ) {
				$actual[] = $pair->getValue();
			}
			sort( $expected );
			sort( $actual );
			self::assertSame( $expected, $actual, "seed $seed, operation $op" );
			self::assertSame( count( $live ), $tree->getSize() );
		}
	}

	/**
	 * @dataProvider provideSeeds
	 */
	public function testRandomInsertRemoveWithDuplicateIntervals( int $seed ): void {
		mt_srand( $seed );
		/** @var IntervalTree<int, int> $tree */
		$tree = new IntervalTree();
		/** @var array<int, array{int, int}> $live */
		$live = [];
		for ( $op = 1; $op <= 300; $op++ ) {
			if ( count( $live ) < 2 || mt_rand( 0, 2 ) > 0 ) {
				$low = mt_rand( 0, 20 );
				$high = $low + mt_rand( 0, 3 );
				$tree->insert( new NumericInterval( $low, $high ), $op );
				$live[$op] = [ $low, $high ];
			} else {
				$value = array_rand( $live );
				$interval = new NumericInterval( ...$live[$value] );
				self::assertTrue( $tree->exist( $interval, $value ), "seed $seed, operation $op" );
				self::assertTrue( $tree->remove( $interval, $value ), "seed $seed, operation $op" );
				self::assertFalse( $tree->exist( $interval, $value ), "seed $seed, operation $op" );
				unset( $live[$value] );
			}

			$queryLow = mt_rand( 0, 25 );
			$queryHigh = $queryLow + mt_rand( 0, 5 );
			$expected = [];
			foreach ( $live as $value => [ $low, $high ] ) {
				if ( !( $high < $queryLow || $queryHigh < $low ) ) {
					$expected[] = $value;
				}
			}
			$actual = [];
			foreach ( $tree->findIntersections( new NumericInterval( $queryLow, $queryHigh ) ) as $pair ) {
				$actual[] = $pair->getValue();
			}
			sort( $expected );
			sort( $actual );
			self::assertSame( $expected, $actual, "seed $seed, operation $op" );
			self::assertSame( count( $live ), $tree->getSize() );
		}
	}
}
