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

namespace Wikimedia\IntervalTree;

use Wikimedia\IntervalTree\Interval\IntervalInterface;

/**
 * @template TPoint
 * @template TValue
 */
final class Node {
	/**
	 * @var Node<TPoint, TValue>
	 */
	private $left;

	/**
	 * @var Node<TPoint, TValue>
	 */
	private $right;

	/**
	 * @var Node<TPoint, TValue>|null
	 */
	private $parent;

	/**
	 * @var NodeColor
	 */
	private $color;

	/**
	 * @var Pair<TPoint, TValue>
	 */
	private $pair;

	/**
	 * @var null|IntervalInterface<TPoint>
	 */
	private $max;

	/**
	 * Phan can't infer the template types of a constructor without parameters.
	 * @suppress PhanGenericConstructorTypes
	 */
	private function __construct() {
	}

	/**
	 * @template TPairPoint
	 * @template TPairValue
	 * @param Pair<TPairPoint, TPairValue> $pair
	 * @return Node<TPairPoint, TPairValue>
	 */
	public static function withPair( Pair $pair ): self {
		$self = new self();
		$self->pair = $pair;
		$self->max = $self->pair->getInterval();

		return $self;
	}

	/**
	 * @return Node<TPoint, TValue>
	 */
	public static function nil(): self {
		$self = new self();
		$self->color = NodeColor::black();
		return $self;
	}

	public function setColor( NodeColor $color ): void {
		$this->color = $color;
	}

	public function getColor(): NodeColor {
		return $this->color;
	}

	/**
	 * @return Node<TPoint, TValue>
	 */
	public function getLeft(): Node {
		return $this->left;
	}

	/**
	 * @param Node<TPoint, TValue> $node
	 * @return void
	 */
	public function setLeft( Node $node ): void {
		$this->left = $node;
	}

	/**
	 * @return Node<TPoint, TValue>
	 */
	public function getRight(): Node {
		return $this->right;
	}

	/**
	 * @param Node<TPoint, TValue> $node
	 * @return void
	 */
	public function setRight( Node $node ): void {
		$this->right = $node;
	}

	/**
	 * @return Node<TPoint, TValue>|null
	 */
	public function getParent(): ?Node {
		return $this->parent;
	}

	/**
	 * @param Node<TPoint, TValue>|null $node
	 * @return void
	 */
	public function setParent( ?Node $node ): void {
		$this->parent = $node;
	}

	/**
	 * @return Pair<TPoint, TValue>
	 */
	public function getPair(): Pair {
		return $this->pair;
	}

	/**
	 * @param Node<TPoint, TValue> $otherNode
	 * @return bool
	 */
	public function lessThan( Node $otherNode ): bool {
		return $this->getPair()->getInterval()->lessThan( $otherNode->getPair()->getInterval() );
	}

	/**
	 * @param Node<TPoint, TValue> $otherNode
	 * @return bool
	 */
	public function equalTo( Node $otherNode ): bool {
		return $this->getPair()->getInterval()->equalTo( $otherNode->getPair()->getInterval() )
			&& $this->getPair()->getValue() === $otherNode->getPair()->getValue();
	}

	/**
	 * @param IntervalInterface<TPoint> $interval
	 * @return bool
	 */
	public function intersect( IntervalInterface $interval ): bool {
		return $this->getPair()->getInterval()->intersect( $interval );
	}

	/**
	 * @param Node<TPoint, TValue> $otherNode
	 * @return void
	 */
	public function copyPairFrom( Node $otherNode ): void {
		$this->pair = $otherNode->getPair();
	}

	/**
	 * @return void
	 */
	public function updateMax(): void {
		$this->max = $this->getPair()->getInterval();
		if ( $this->getRight()->max !== null ) {
			$this->max = $this->max->merge( $this->getRight()->max );
		}
		if ( $this->getLeft()->max !== null ) {
			$this->max = $this->max->merge( $this->getLeft()->max );
		}
	}

	/**
	 * @param IntervalInterface<TPoint> $interval
	 * @return bool
	 */
	public function notIntersectLeftSubtree( IntervalInterface $interval ): bool {
		$high = $this->getLeft()->max->getHigh() ?? $this->getLeft()->getPair()->getInterval()->getHigh();
		return $high < $interval->getLow();
	}

	/**
	 * @param IntervalInterface<TPoint> $interval
	 * @return bool
	 */
	public function notIntersectRightSubtree( IntervalInterface $interval ): bool {
		$low = $this->getRight()->max->getLow() ?? $this->getRight()->getPair()->getInterval()->getLow();
		return $interval->getHigh() < $low;
	}
}
