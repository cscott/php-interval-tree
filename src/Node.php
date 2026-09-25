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
	 * The lowest low point in this node's subtree, or null for the nil node.
	 * (Phan doesn't carry TPoint through IntervalInterface::getLow().)
	 * @var TPoint|null
	 * @phan-var mixed
	 */
	private $minLow;

	/**
	 * The highest high point in this node's subtree, or null for the nil node.
	 * @var TPoint|null
	 * @phan-var mixed
	 */
	private $maxHigh;

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
		$self->minLow = $pair->getInterval()->getLow();
		$self->maxHigh = $pair->getInterval()->getHigh();

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
	 * Recompute the lowest low and highest high points of this node's
	 * subtree from its own interval and its children's.
	 *
	 * @return void
	 */
	public function updateMax(): void {
		$interval = $this->getPair()->getInterval();
		$this->minLow = $interval->getLow();
		$this->maxHigh = $interval->getHigh();
		foreach ( [ $this->getLeft(), $this->getRight() ] as $child ) {
			if ( $child->maxHigh === null ) {
				// The nil node
				continue;
			}
			if ( $child->minLow < $this->minLow ) {
				$this->minLow = $child->minLow;
			}
			if ( $child->maxHigh > $this->maxHigh ) {
				$this->maxHigh = $child->maxHigh;
			}
		}
	}

	/**
	 * Returns true if no interval in the left subtree can intersect the
	 * given one: the subtree's intervals all lie outside the closed range
	 * [$interval->getLow(), $interval->getHigh()].
	 *
	 * @param IntervalInterface<TPoint> $interval
	 * @return bool
	 */
	public function notIntersectLeftSubtree( IntervalInterface $interval ): bool {
		return $this->outsideSubtree( $this->getLeft(), $interval );
	}

	/**
	 * Returns true if no interval in the right subtree can intersect the
	 * given one; see notIntersectLeftSubtree().
	 *
	 * @param IntervalInterface<TPoint> $interval
	 * @return bool
	 */
	public function notIntersectRightSubtree( IntervalInterface $interval ): bool {
		return $this->outsideSubtree( $this->getRight(), $interval );
	}

	/**
	 * @param Node<TPoint, TValue> $subtree
	 * @param IntervalInterface<TPoint> $interval
	 * @return bool
	 */
	private function outsideSubtree( Node $subtree, IntervalInterface $interval ): bool {
		return $subtree->maxHigh < $interval->getLow() || $interval->getHigh() < $subtree->minLow;
	}
}
