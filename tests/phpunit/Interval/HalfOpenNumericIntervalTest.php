<?php

declare( strict_types=1 );

namespace Wikimedia\IntervalTree\Tests\Interval;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Wikimedia\IntervalTree\Interval\HalfOpenNumericInterval;
use Wikimedia\IntervalTree\Interval\NumericInterval;
use Wikimedia\IntervalTree\IntervalTree;

/**
 * @covers \Wikimedia\IntervalTree\Interval\HalfOpenNumericInterval
 */
final class HalfOpenNumericIntervalTest extends TestCase {
	public function testConstruct(): void {
		$interval = new HalfOpenNumericInterval( 1, 2 );
		self::assertSame( 1, $interval->getLow() );
		self::assertSame( 2, $interval->getHigh() );
		self::assertFalse( $interval->isEmpty() );
		self::assertTrue( ( new HalfOpenNumericInterval( 3, 3 ) )->isEmpty() );

		$this->expectException( InvalidArgumentException::class );
		new HalfOpenNumericInterval( 2, 1 );
	}

	public function testFromArray(): void {
		$interval = HalfOpenNumericInterval::fromArray( [ 20.5, 25 ] );
		self::assertSame( 20.5, $interval->getLow() );
		self::assertSame( 25, $interval->getHigh() );

		$this->expectException( InvalidArgumentException::class );
		HalfOpenNumericInterval::fromArray( [ 1 ] );
	}

	public function testOrdering(): void {
		$a = new HalfOpenNumericInterval( 1, 5 );
		self::assertTrue( $a->lessThan( new HalfOpenNumericInterval( 2, 3 ) ) );
		self::assertTrue( $a->lessThan( new HalfOpenNumericInterval( 1, 6 ) ) );
		self::assertFalse( $a->lessThan( new HalfOpenNumericInterval( 1, 5 ) ) );
		self::assertTrue( $a->equalTo( new HalfOpenNumericInterval( 1, 5 ) ) );
		self::assertFalse( $a->equalTo( new HalfOpenNumericInterval( 1, 4 ) ) );
	}

	/**
	 * @return array<string, array{array{int, int}, array{int, int}, bool}>
	 */
	public static function provideIntersect(): array {
		return [
			'overlapping' => [ [ 1, 5 ], [ 3, 8 ], true ],
			'nested' => [ [ 1, 9 ], [ 3, 4 ], true ],
			'equal' => [ [ 1, 5 ], [ 1, 5 ], true ],
			'touching' => [ [ 1, 5 ], [ 5, 8 ], false ],
			'disjoint' => [ [ 1, 3 ], [ 5, 8 ], false ],
			'point at low end' => [ [ 1, 5 ], [ 1, 1 ], true ],
			'point inside' => [ [ 1, 5 ], [ 3, 3 ], true ],
			'point at high end' => [ [ 1, 5 ], [ 5, 5 ], false ],
			'point outside' => [ [ 1, 5 ], [ 7, 7 ], false ],
			'same point' => [ [ 4, 4 ], [ 4, 4 ], true ],
			'different points' => [ [ 4, 4 ], [ 5, 5 ], false ],
		];
	}

	/**
	 * @dataProvider provideIntersect
	 * @param array{int, int} $a
	 * @param array{int, int} $b
	 * @param bool $expected
	 */
	public function testIntersect( array $a, array $b, bool $expected ): void {
		$a = HalfOpenNumericInterval::fromArray( $a );
		$b = HalfOpenNumericInterval::fromArray( $b );
		self::assertSame( $expected, $a->intersect( $b ) );
		self::assertSame( $expected, $b->intersect( $a ), 'intersect() is symmetric' );
	}

	/**
	 * @uses \Wikimedia\IntervalTree\Interval\NumericInterval
	 */
	public function testIntersectTreatsOtherClassesAsHalfOpen(): void {
		$interval = new HalfOpenNumericInterval( 1, 5 );
		self::assertFalse( $interval->intersect( new NumericInterval( 5, 8 ) ) );
		self::assertTrue( $interval->intersect( new NumericInterval( 3, 3 ) ) );
	}

	/**
	 * @return array<string, array{int}>
	 */
	public static function provideSeeds(): array {
		$seeds = [];
		for ( $seed = 1; $seed <= 20; $seed++ ) {
			$seeds["seed $seed"] = [ $seed ];
		}
		return $seeds;
	}

	/**
	 * Store many touching and empty intervals in a tree, and compare
	 * findIntersections() with brute force, including point queries.
	 *
	 * @dataProvider provideSeeds
	 * @covers \Wikimedia\IntervalTree\IntervalTree
	 * @uses \Wikimedia\IntervalTree\Node
	 * @uses \Wikimedia\IntervalTree\NodeColor
	 * @uses \Wikimedia\IntervalTree\Pair
	 */
	public function testInTreeMatchesBruteForce( int $seed ): void {
		mt_srand( $seed );
		$tree = new IntervalTree();
		/** @var array<int, array{int, int}> $live */
		$live = [];
		for ( $op = 1; $op <= 200; $op++ ) {
			if ( count( $live ) < 2 || mt_rand( 0, 2 ) > 0 ) {
				$low = mt_rand( 0, 20 );
				// About a third of the intervals are empty.
				$high = $low + max( 0, mt_rand( -2, 4 ) );
				$tree->insert( new HalfOpenNumericInterval( $low, $high ), $op );
				$live[$op] = [ $low, $high ];
			} else {
				$value = array_rand( $live );
				self::assertTrue( $tree->remove( HalfOpenNumericInterval::fromArray( $live[$value] ), $value ) );
				unset( $live[$value] );
			}

			$queryLow = mt_rand( 0, 25 );
			$queryHigh = $queryLow + max( 0, mt_rand( -2, 4 ) );
			$query = new HalfOpenNumericInterval( $queryLow, $queryHigh );
			$expected = [];
			foreach ( $live as $value => [ $low, $high ] ) {
				if ( self::bruteForceIntersect( $low, $high, $queryLow, $queryHigh ) ) {
					$expected[] = $value;
				}
			}
			$actual = [];
			foreach ( $tree->findIntersections( $query ) as $pair ) {
				$actual[] = $pair->getValue();
			}
			sort( $expected );
			sort( $actual );
			self::assertSame( $expected, $actual, "seed $seed, operation $op" );
		}
	}

	/**
	 * Intersection of [a, b) and [c, d), with empty intervals as points,
	 * written independently of HalfOpenNumericInterval::intersect().
	 */
	private static function bruteForceIntersect( int $a, int $b, int $c, int $d ): bool {
		$first = $a === $b ? [ $a ] : range( $a, $b - 1 );
		$second = $c === $d ? [ $c ] : range( $c, $d - 1 );
		return (bool)array_intersect( $first, $second );
	}
}
