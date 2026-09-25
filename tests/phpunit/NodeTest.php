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

namespace Wikimedia\IntervalTree\Tests;

use PHPUnit\Framework\TestCase;
use Wikimedia\IntervalTree\Interval\NumericInterval;
use Wikimedia\IntervalTree\Node;
use Wikimedia\IntervalTree\NodeColor;
use Wikimedia\IntervalTree\Pair;

/**
 * @covers \Wikimedia\IntervalTree\Node
 */
class NodeTest extends TestCase {
	/**
	 * @uses \Wikimedia\IntervalTree\Pair
	 * @uses \Wikimedia\IntervalTree\Interval\NumericInterval
	 */
	public function testWithPair(): void {
		$node = Node::withPair( new Pair( NumericInterval::fromArray( [ 1, 5 ] ), 'val' ) );
		self::assertSame( 1, $node->getPair()->getInterval()->getLow() );
		self::assertEquals( 5, $node->getPair()->getInterval()->getHigh() );
		self::assertEquals( 'val', $node->getPair()->getValue() );
	}

	/**
	 * @uses \Wikimedia\IntervalTree\NodeColor
	 */
	public function testNil(): void {
		$node = Node::nil();
		self::assertTrue( $node->getColor()->isBlack() );
		self::assertNull( $node->getParent() );
	}

	/**
	 * @uses \Wikimedia\IntervalTree\Pair
	 * @uses \Wikimedia\IntervalTree\Interval\NumericInterval
	 */
	public function testGetParent(): void {
		$node = Node::withPair( new Pair( NumericInterval::fromArray( [ 1, 5 ] ) ) );
		$parentNode = Node::withPair( new Pair( NumericInterval::fromArray( [ 1, 2 ] ) ) );
		$node->setParent( $parentNode );
		self::assertSame( $node->getParent(), $parentNode );
	}

	/**
	 * @uses \Wikimedia\IntervalTree\Pair
	 * @uses \Wikimedia\IntervalTree\Interval\NumericInterval
	 */
	public function testCopyPairFrom(): void {
		$node = Node::withPair( new Pair( NumericInterval::fromArray( [ 1, 5 ] ) ) );
		$otherNode = Node::withPair( new Pair( NumericInterval::fromArray( [ 0, 3 ] ) ) );
		$node->copyPairFrom( $otherNode );
		self::assertTrue( $node->equalTo( $otherNode ) );
	}

	/**
	 * @uses \Wikimedia\IntervalTree\Pair
	 * @uses \Wikimedia\IntervalTree\Interval\NumericInterval
	 * @uses \Wikimedia\IntervalTree\NodeColor
	 */
	public function testGetColor(): void {
		$node = Node::withPair( new Pair( NumericInterval::fromArray( [ 1, 5 ] ) ) );
		$node->setColor( NodeColor::red() );
		self::assertTrue( $node->getColor()->isRed() );
		$node->setColor( NodeColor::black() );
		self::assertTrue( $node->getColor()->isBlack() );
	}

	/**
	 * @uses \Wikimedia\IntervalTree\Pair
	 * @uses \Wikimedia\IntervalTree\Interval\NumericInterval
	 */
	public function testEqualTo(): void {
		$node = Node::withPair( new Pair( NumericInterval::fromArray( [ 1, 5 ] ), 'foo' ) );
		$sameNode = Node::withPair( new Pair( NumericInterval::fromArray( [ 1, 5 ] ), 'foo' ) );
		$otherNode = Node::withPair( new Pair( NumericInterval::fromArray( [ 3, 4 ] ), 'bar' ) );
		self::assertTrue( $node->equalTo( $sameNode ) );
		self::assertFalse( $node->equalTo( $otherNode ) );
	}

	/**
	 * @uses \Wikimedia\IntervalTree\Pair
	 * @uses \Wikimedia\IntervalTree\Interval\NumericInterval
	 */
	public function testEqualToComparesFalsyValues(): void {
		$interval = NumericInterval::fromArray( [ 1, 5 ] );
		$makeNode = static function ( $value ) use ( $interval ): Node {
			return Node::withPair( new Pair( $interval, $value ) );
		};
		$node = $makeNode( 'foo' );
		foreach ( [ 0, 0.0, '', '0', null, false, [] ] as $falsy ) {
			$falsyNode = $makeNode( $falsy );
			self::assertFalse( $node->equalTo( $falsyNode ) );
			self::assertFalse( $falsyNode->equalTo( $node ) );
			self::assertTrue( $falsyNode->equalTo( $makeNode( $falsy ) ) );
		}
		self::assertFalse( $makeNode( 0 )->equalTo( $makeNode( null ) ) );
	}

	/**
	 * @uses \Wikimedia\IntervalTree\Pair
	 * @uses \Wikimedia\IntervalTree\Interval\NumericInterval
	 * @covers \Wikimedia\IntervalTree\Node::getRight
	 */
	public function testSetRight(): void {
		$node = Node::withPair( new Pair( NumericInterval::fromArray( [ 1, 5 ] ) ) );
		$rightNode = Node::withPair( new Pair( NumericInterval::fromArray( [ 1, 6 ] ) ) );
		$node->setRight( $rightNode );
		self::assertSame( $node->getRight(), $rightNode );
	}

	/**
	 * @uses \Wikimedia\IntervalTree\Pair
	 * @uses \Wikimedia\IntervalTree\Interval\NumericInterval
	 * @covers \Wikimedia\IntervalTree\Node::getLeft
	 */
	public function testSetLeft(): void {
		$node = Node::withPair( new Pair( NumericInterval::fromArray( [ 1, 5 ] ) ) );
		$leftNode = Node::withPair( new Pair( NumericInterval::fromArray( [ 1, 6 ] ) ) );
		$node->setLeft( $leftNode );
		self::assertSame( $node->getLeft(), $leftNode );
	}
}
