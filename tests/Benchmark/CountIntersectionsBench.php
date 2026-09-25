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

declare( strict_types=1 );

namespace Wikimedia\IntervalTree\Tests\Benchmark;

use PhpBench\Attributes as Bench;
use Wikimedia\IntervalTree\Interval\IntervalInterface;
use Wikimedia\IntervalTree\IntervalTree;

#[Bench\BeforeMethods( [ 'init' ] )]
class CountIntersectionsBench {
	use GenerateIntervalTrait;

	private const AMOUNT_INTERVALS_IN_TREE = 10000;
	private const MAX_INTERVAL_HIGH        = 250000;
	private const MAX_INTERVAL_OFFSET      = 100;

	/**
	 * @var IntervalTree<int, null>
	 */
	private $tree;

	/**
	 * @var IntervalInterface<int>[]
	 */
	private $bruteForceList;

	public function init(): void {
		$this->tree = new IntervalTree();
		$this->bruteForceList = [];

		for ( $i = 0; $i < self::AMOUNT_INTERVALS_IN_TREE; $i++ ) {
			$interval = $this->generateInterval( self::MAX_INTERVAL_HIGH, self::MAX_INTERVAL_OFFSET );
			$this->tree->insert( $interval );
			$this->bruteForceList[] = $interval;
		}
	}

	#[Bench\Revs( 10 )]
	public function benchTree(): void {
		$searchedInterval = $this->generateInterval( self::MAX_INTERVAL_HIGH, self::MAX_INTERVAL_OFFSET );
		$this->tree->countIntersections( $searchedInterval );
	}

	#[Bench\Revs( 10 )]
	public function benchBruteForce(): void {
		$searchedInterval = $this->generateInterval( self::MAX_INTERVAL_HIGH, self::MAX_INTERVAL_OFFSET );
		foreach ( $this->bruteForceList as $interval ) {
			$interval->intersect( $searchedInterval );
		}
	}
}
