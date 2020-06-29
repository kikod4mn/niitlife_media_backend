<?php

declare(strict_types = 1);

namespace App\Entity\Concerns;

use Doctrine\ORM\Mapping as ORM;

trait CountableLikesTrait
{
	/**
	 * @ORM\Column(type="bigint", nullable=false)
	 * @var int
	 */
	protected int $likeCount = 0;
	
	/**
	 * @ORM\Column(type="bigint", nullable=false)
	 * @var int
	 */
	protected int $weeklyLikeCount = 0;
	
	/**
	 * @return null|int
	 */
	public function getLikeCount(): ?int
	{
		return $this->likeCount;
	}
	
	/**
	 * @return null|int
	 */
	public function getWeeklyLikeCount(): ?int
	{
		return $this->weeklyLikeCount;
	}
	
	/**
	 * Increment the like counters.
	 * @return void
	 */
	public function incrementLikeCounters(): void
	{
		$this->likeCount++;
		$this->weeklyLikeCount++;
	}
	
	/**
	 * Reset only the weekly counter. No point in gathering all time count if we reset it weekly!
	 * @return void
	 */
	public function resetWeeklyLikeCounter(): void
	{
		$this->weeklyLikeCount = 0;
	}
}