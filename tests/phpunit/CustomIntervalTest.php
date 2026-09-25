<?php

declare( strict_types=1 );

namespace Wikimedia\IntervalTree\Tests;

use PHPUnit\Framework\TestCase;
use Wikimedia\IntervalTree\Interval\IntervalInterface;
use Wikimedia\IntervalTree\Interval\NumericInterval;
use Wikimedia\IntervalTree\IntervalTree;
use Wikimedia\IntervalTree\Pair;
use Wikimedia\IntervalTree\Tests\Fixtures\LabeledInterval;

/**
 * Checks that the tree works with a custom IntervalInterface
 * implementation that follows the documented contract but otherwise
 * behaves quite differently from the built-in interval types.
 *
 * @covers \Wikimedia\IntervalTree\IntervalTree
 * @uses \Wikimedia\IntervalTree\Interval\NumericInterval
 * @uses \Wikimedia\IntervalTree\Node
 * @uses \Wikimedia\IntervalTree\NodeColor
 * @uses \Wikimedia\IntervalTree\Pair
 */
final class CustomIntervalTest extends TestCase {

	private const LABELS = [ 'a', 'b', 'c' ];

	public function testQueryKindSelectsSemantics(): void {
		$tree = new IntervalTree();
		$tree->insert( new LabeledInterval( 1, 5, 'a' ), 'first' );
		$tree->insert( new LabeledInterval( 3, 8, 'b' ), 'second' );
		$tree->insert( new LabeledInterval( 10, 12, 'a' ), 'third' );

		// A plain interval matches on range alone; results come in the
		// custom order (by high point, descending).
		self::assertSame(
			[ 'second', 'first' ],
			self::values( $tree->findIntersections( new NumericInterval( 4, 6 ) ) )
		);
		// A LabeledInterval also has to match the label.
		self::assertSame(
			[ 'first' ],
			self::values( $tree->findIntersections( new LabeledInterval( 4, 6, 'a' ) ) )
		);
		self::assertTrue( $tree->exist( new LabeledInterval( 10, 12, 'a' ), 'third' ) );
		self::assertFalse( $tree->exist( new LabeledInterval( 10, 12, 'b' ), 'third' ) );
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
	 * @dataProvider provideSeeds
	 */
	public function testRandomOperationsMatchBruteForce( int $seed ): void {
		mt_srand( $seed );
		$tree = new IntervalTree();
		/** @var array<int, LabeledInterval> $live */
		$live = [];
		for ( $op = 1; $op <= 300; $op++ ) {
			if ( count( $live ) < 2 || mt_rand( 0, 2 ) > 0 ) {
				// Small domain, so there are many equal ranges and labels.
				$low = mt_rand( 0, 30 );
				$interval = new LabeledInterval(
					$low, $low + mt_rand( 0, 6 ), self::LABELS[ mt_rand( 0, 2 ) ]
				);
				$tree->insert( $interval, $op );
				$live[$op] = $interval;
			} else {
				$value = array_rand( $live );
				self::assertTrue( $tree->exist( $live[$value], $value ), "seed $seed, operation $op" );
				self::assertTrue( $tree->remove( $live[$value], $value ), "seed $seed, operation $op" );
				self::assertFalse( $tree->exist( $live[$value], $value ), "seed $seed, operation $op" );
				unset( $live[$value] );
			}

			$queryLow = mt_rand( 0, 36 );
			$queryHigh = $queryLow + mt_rand( 0, 4 );
			$queries = [
				new NumericInterval( $queryLow, $queryHigh ),
				new LabeledInterval( $queryLow, $queryHigh, self::LABELS[ mt_rand( 0, 2 ) ] ),
			];
			foreach ( $queries as $query ) {
				self::assertSame(
					self::bruteForce( $live, $query ),
					self::values( $tree->findIntersections( $query ) ),
					"seed $seed, operation $op"
				);
			}
			self::assertSame( count( $live ), $tree->getSize() );
		}
	}

	/**
	 * The values of the stored intervals that intersect $query, in the
	 * intervals' order (values of equal intervals in any order, so sort
	 * those).
	 *
	 * @param array<int, LabeledInterval> $live
	 * @param IntervalInterface<int> $query
	 * @return list<int>
	 */
	private static function bruteForce( array $live, $query ): array {
		$matches = array_filter( $live, static function ( LabeledInterval $interval ) use ( $query ) {
			return $interval->intersect( $query );
		} );
		uksort( $matches, static function ( int $a, int $b ) use ( $matches ) {
			if ( $matches[$a]->lessThan( $matches[$b] ) ) {
				return -1;
			}
			if ( $matches[$b]->lessThan( $matches[$a] ) ) {
				return 1;
			}
			return $a <=> $b;
		} );
		return array_keys( $matches );
	}

	/**
	 * The values of the pairs, with runs of equal intervals sorted by
	 * value so that they compare equal to bruteForce().
	 *
	 * @param iterable<Pair<int, mixed>> $pairs
	 * @return list<mixed>
	 */
	private static function values( iterable $pairs ): array {
		$runs = [];
		$previous = null;
		foreach ( $pairs as $pair ) {
			$interval = $pair->getInterval();
			if ( $previous === null || !$previous->equalTo( $interval ) ) {
				$runs[] = [];
			}
			$runs[ count( $runs ) - 1 ][] = $pair->getValue();
			$previous = $interval;
		}
		$values = [];
		foreach ( $runs as $run ) {
			sort( $run );
			array_push( $values, ...$run );
		}
		return $values;
	}
}
