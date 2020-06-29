<?php

declare(strict_types = 1);

namespace App\Entity\Contracts;

interface Likeable
{
	/**
	 * @return null|int
	 */
	public function getLikeCount(): ?int;
	
	/**
	 * @return null|int
	 */
	public function getWeeklyLikeCount(): ?int;
	
	/**
	 * Increment the like counters.
	 * @return void
	 */
	public function incrementLikeCounters(): void;
	
	/**
	 * Reset only the weekly counter. No point in gathering all time count if we reset it weekly!
	 * @return void
	 */
	public function resetWeeklyLikeCounter(): void;
}