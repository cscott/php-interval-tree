<?php

declare( strict_types=1 );

namespace Wikimedia\IntervalTree\Tests;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Wikimedia\IntervalTree\Interval\DateTimeInterval;
use Wikimedia\IntervalTree\Interval\HalfOpenNumericInterval;
use Wikimedia\IntervalTree\Interval\NumericInterval;
use Wikimedia\IntervalTree\IntervalTree;

/**
 * Runs every code example in README.md, so that the documentation can't
 * go stale.
 *
 * KEEP IN SYNC WITH README.md: each test below mirrors the README section
 * named in its comment, with the results shown in the README's comments
 * turned into assertions.  If you change a test here, make the same change
 * to the README, and vice versa.
 *
 * @covers \Wikimedia\IntervalTree\IntervalTree
 * @covers \Wikimedia\IntervalTree\Interval\NumericInterval
 * @covers \Wikimedia\IntervalTree\Interval\DateTimeInterval
 * @covers \Wikimedia\IntervalTree\Interval\HalfOpenNumericInterval
 * @uses \Wikimedia\IntervalTree\Node
 * @uses \Wikimedia\IntervalTree\NodeColor
 * @uses \Wikimedia\IntervalTree\Pair
 */
final class ReadmeExamplesTest extends TestCase {

	/**
	 * README.md: "Usage" > "Interval Tree".  Keep in sync with README.md.
	 */
	public function testIntervalTreeUsage(): void {
		// #### insert(IntervalInterface $interval, mixed $value): void
		$tree = new IntervalTree();
		$tree->insert( new NumericInterval( 1, 10 ), 'val1' );
		$tree->insert( new NumericInterval( 2, 5 ), 'val2' );
		$tree->insert( new NumericInterval( 11, 12 ), 'val3' );

		// #### findIntersections(IntervalInterface $interval): Iterator\<Pair>
		$intersections = $tree->findIntersections( new NumericInterval( 3, 5 ) );
		$lows = [];
		$highs = [];
		$values = [];
		foreach ( $intersections as $pair ) {
			$lows[] = $pair->getInterval()->getLow();
			$highs[] = $pair->getInterval()->getHigh();
			$values[] = $pair->getValue();
		}
		self::assertSame( [ 1, 2 ], $lows );
		self::assertSame( [ 10, 5 ], $highs );
		self::assertSame( [ 'val1', 'val2' ], $values );

		// #### hasIntersection(IntervalInterface $interval): bool
		self::assertTrue( $tree->hasIntersection( new NumericInterval( 3, 5 ) ) );
		self::assertFalse( $tree->hasIntersection( new NumericInterval( 20, 30 ) ) );

		// #### countIntersections(IntervalInterface $interval): int
		self::assertSame( 2, $tree->countIntersections( new NumericInterval( 3, 5 ) ) );

		// #### exist(IntervalInterface $interval, $value): bool
		self::assertTrue( $tree->exist( new NumericInterval( 11, 12 ), 'val3' ) );
		self::assertFalse( $tree->exist( new NumericInterval( 11, 12 ), 'val1' ) );

		// #### isEmpty(): bool
		self::assertFalse( $tree->isEmpty() );

		// #### getSize(): int
		self::assertSame( 3, $tree->getSize() );

		// #### iterateFrom(?IntervalInterface $from = null): Iterator\<Pair>
		$values = [];
		foreach ( $tree->iterateFrom( new NumericInterval( 2, 2 ) ) as $pair ) {
			$values[] = $pair->getValue();
		}
		self::assertSame( [ 'val2', 'val3' ], $values );

		// #### iterateBefore(?IntervalInterface $before = null): Iterator\<Pair>
		$values = [];
		foreach ( $tree->iterateBefore( new NumericInterval( 11, 11 ) ) as $pair ) {
			$values[] = $pair->getValue();
		}
		self::assertSame( [ 'val2', 'val1' ], $values );

		// #### remove(IntervalInterface $interval, $value): bool
		self::assertTrue( $tree->remove( new NumericInterval( 11, 12 ), 'val3' ) );
		self::assertFalse( $tree->remove( new NumericInterval( 11, 12 ), 'val3' ) );
		self::assertSame( 2, $tree->getSize() );
	}

	/**
	 * README.md: "Usage" > "Intervals" > "Numeric interval".
	 * Keep in sync with README.md.
	 */
	public function testNumericIntervalUsage(): void {
		// Instantiate numeric interval from array
		$numericInterval = NumericInterval::fromArray( [ 1, 100 ] );
		self::assertSame( 1, $numericInterval->getLow() );
		self::assertSame( 100, $numericInterval->getHigh() );

		// Instantiate numeric interval with constructor
		$numericInterval = new NumericInterval( 1, 100 );
		self::assertSame( 1, $numericInterval->getLow() );
		self::assertSame( 100, $numericInterval->getHigh() );

		// Floats work too
		$numericInterval = new NumericInterval( 0.5, 99.5 );
		self::assertSame( 0.5, $numericInterval->getLow() );
		self::assertSame( 99.5, $numericInterval->getHigh() );

		// Both constructors throw an InvalidArgumentException if the low
		// end is greater than the high end.
		$this->expectException( InvalidArgumentException::class );
		new NumericInterval( 100, 1 );
	}

	/**
	 * README.md: "Usage" > "Intervals" > "DateTime interval".
	 * Keep in sync with README.md.
	 */
	public function testDateTimeIntervalUsage(): void {
		// Instantiate DateTime interval from array
		$dateTimeInterval = DateTimeInterval::fromArray( [
			new DateTimeImmutable( '2021-01-01 00:00:00' ),
			new DateTimeImmutable( '2021-01-02 00:00:00' ),
		] );
		self::assertEquals( new DateTimeImmutable( '2021-01-01 00:00:00' ), $dateTimeInterval->getLow() );
		self::assertEquals( new DateTimeImmutable( '2021-01-02 00:00:00' ), $dateTimeInterval->getHigh() );

		// Instantiate DateTime interval with constructor
		$dateTimeInterval = new DateTimeInterval(
			new DateTimeImmutable( '2021-01-01 00:00:00' ),
			new DateTimeImmutable( '2021-01-02 00:00:00' )
		);
		self::assertEquals( new DateTimeImmutable( '2021-01-01 00:00:00' ), $dateTimeInterval->getLow() );
		self::assertEquals( new DateTimeImmutable( '2021-01-02 00:00:00' ), $dateTimeInterval->getHigh() );

		// Both constructors throw an InvalidArgumentException if the low
		// end is greater than the high end.
		$this->expectException( InvalidArgumentException::class );
		new DateTimeInterval(
			new DateTimeImmutable( '2021-01-02 00:00:00' ),
			new DateTimeImmutable( '2021-01-01 00:00:00' )
		);
	}

	/**
	 * README.md: "Usage" > "Intervals" > "Half-open numeric interval".
	 * Keep in sync with README.md.
	 */
	public function testHalfOpenNumericIntervalUsage(): void {
		$tree = new IntervalTree();
		$tree->insert( new HalfOpenNumericInterval( 0, 5 ), 'first' );
		$tree->insert( new HalfOpenNumericInterval( 5, 10 ), 'second' );
		$tree->insert( new HalfOpenNumericInterval( 5, 5 ), 'marker' );

		// [0, 5) and [5, 10) don't intersect
		self::assertSame( 1, $tree->countIntersections( new HalfOpenNumericInterval( 0, 5 ) ) );

		// Find everything that contains the point 5
		$values = [];
		foreach ( $tree->findIntersections( new HalfOpenNumericInterval( 5, 5 ) ) as $pair ) {
			$values[] = $pair->getValue();
		}
		self::assertSame( [ 'marker', 'second' ], $values );
	}

	/**
	 * README.md: "Examples" > "Finding overlapping bookings".
	 * Keep in sync with README.md.
	 */
	public function testFindingOverlappingBookings(): void {
		$bookings = new IntervalTree();
		$bookings->insert( new DateTimeInterval(
			new DateTimeImmutable( '2026-10-01 09:00' ),
			new DateTimeImmutable( '2026-10-01 09:15' )
		), 'Standup' );
		$bookings->insert( new DateTimeInterval(
			new DateTimeImmutable( '2026-10-01 11:00' ),
			new DateTimeImmutable( '2026-10-01 12:30' )
		), 'Design review' );
		$bookings->insert( new DateTimeInterval(
			new DateTimeImmutable( '2026-10-01 12:00' ),
			new DateTimeImmutable( '2026-10-01 13:00' )
		), 'Lunch' );

		$proposed = new DateTimeInterval(
			new DateTimeImmutable( '2026-10-01 12:15' ),
			new DateTimeImmutable( '2026-10-01 12:45' )
		);
		$conflicts = [];
		foreach ( $bookings->findIntersections( $proposed ) as $pair ) {
			$conflicts[] = $pair->getValue();
		}
		self::assertSame( [ 'Design review', 'Lunch' ], $conflicts );
	}

	/**
	 * README.md: "Examples" > "Annotations on a range of text".
	 * Keep in sync with README.md.
	 */
	public function testAnnotationsOnARangeOfText(): void {
		$annotations = new IntervalTree();
		$annotations->insert( new NumericInterval( 0, 4 ), [ 'type' => 'bold' ] );
		$annotations->insert( new NumericInterval( 3, 9 ), [ 'type' => 'link', 'href' => 'Main_Page' ] );
		$annotations->insert( new NumericInterval( 12, 20 ), [ 'type' => 'italic' ] );
		$annotations->insert( new NumericInterval( 25, 30 ), [ 'type' => 'bold' ] );

		$types = [];
		foreach ( $annotations->findIntersections( new NumericInterval( 4, 12 ) ) as $pair ) {
			$types[] = $pair->getValue()['type'];
		}
		self::assertSame( [ 'bold', 'link', 'italic' ], $types );
	}

	/**
	 * README.md: "Examples" > "Several values for the same interval".
	 * Keep in sync with README.md.
	 */
	public function testSeveralValuesForTheSameInterval(): void {
		$tree = new IntervalTree();
		$tree->insert( new NumericInterval( 1, 5 ), 'a' );
		$tree->insert( new NumericInterval( 1, 5 ), 'b' );
		$tree->insert( new NumericInterval( 1, 5 ), 'c' );
		self::assertSame( 3, $tree->countIntersections( new NumericInterval( 2, 3 ) ) );

		// Values are compared strictly, so this doesn't match 'a', 'b' or 'c'
		self::assertFalse( $tree->remove( new NumericInterval( 1, 5 ), 0 ) );

		self::assertTrue( $tree->remove( new NumericInterval( 1, 5 ), 'b' ) );
		self::assertTrue( $tree->exist( new NumericInterval( 1, 5 ), 'a' ) );
		self::assertFalse( $tree->exist( new NumericInterval( 1, 5 ), 'b' ) );
		self::assertSame( 2, $tree->countIntersections( new NumericInterval( 2, 3 ) ) );
	}
}
