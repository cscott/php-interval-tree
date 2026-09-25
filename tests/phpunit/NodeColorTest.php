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
use Wikimedia\IntervalTree\NodeColor;

/**
 * @covers \Wikimedia\IntervalTree\NodeColor
 */
final class NodeColorTest extends TestCase {
	public function testBlack(): void {
		$nodeColor = NodeColor::black();
		self::assertTrue( $nodeColor->isBlack() );
	}

	public function testRed(): void {
		$nodeColor = NodeColor::red();
		self::assertTrue( $nodeColor->isRed() );
	}
}
