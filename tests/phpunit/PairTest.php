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
use Wikimedia\IntervalTree\Pair;

/**
 * @covers \Wikimedia\IntervalTree\Pair
 */
final class PairTest extends TestCase {
	private const EXAMPLE_INTERVAL = [ 1, 5 ];
	private const EXAMPLE_VALUE = '1_5';

	/**
	 * @var Pair<int, string>
	 */
	protected $pair;

	public function setUp(): void {
		$this->pair = new Pair(
			NumericInterval::fromArray( self::EXAMPLE_INTERVAL ),
			self::EXAMPLE_VALUE
		);

		parent::setUp();
	}

	/**
	 * @uses \Wikimedia\IntervalTree\Interval\NumericInterval
	 */
	public function testGetInterval(): void {
		self::assertTrue(
			$this->pair->getInterval()->equalTo(
				NumericInterval::fromArray( self::EXAMPLE_INTERVAL )
			)
		);
	}

	/**
	 * @uses \Wikimedia\IntervalTree\Interval\NumericInterval
	 */
	public function testGetValue(): void {
		self::assertSame( self::EXAMPLE_VALUE, $this->pair->getValue() );
	}
}
