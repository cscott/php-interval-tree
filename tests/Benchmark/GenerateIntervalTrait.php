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

use Exception;
use InvalidArgumentException;
use Wikimedia\IntervalTree\Interval\IntervalInterface;
use Wikimedia\IntervalTree\Interval\NumericInterval;

trait GenerateIntervalTrait {
	/**
	 * @param int $maxHigh
	 * @param int $maxOffset
	 * @return IntervalInterface<int>
	 */
	private function generateInterval( int $maxHigh, int $maxOffset ): IntervalInterface {
		try {
			$low = random_int( 0, $maxHigh );
			$high = random_int( $low, min( $low + $maxOffset, $maxHigh ) );
		} catch ( Exception $exception ) {
			throw new InvalidArgumentException( 'Wrong interval arguments', $exception->getCode(), $exception );
		}

		return NumericInterval::fromArray( [ $low, $high ] );
	}
}
