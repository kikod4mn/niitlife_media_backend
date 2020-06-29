<?php

declare(strict_types = 1);

namespace App\Entity\Concerns;

use Doctrine\ORM\Mapping as ORM;

trait CountableViewsTrait
{
	/**
	 * @ORM\Column(type="bigint", nullable=false)
	 * @var int
	 */
	protected int $viewCount = 0;
	
	/**
	 * @ORM\Column(type="bigint", nullable=false)
	 * @var int
	 */
	protected int $weeklyViewCount = 0;
	
	/**
	 * @ORM\Column(type="bigint", nullable=false)
	 * @var int
	 */
	protected int $monthlyViewCount = 0;
	
	/**
	 * @return null|int
	 */
	public function getViewCount(): ?int
	{
		return $this->viewCount;
	}
	
	/**
	 * Increment all view counters.
	 * @return void
	 */
	public function incrementViewCounters(): void
	{
		$this->weeklyViewCount++;
		$this->viewCount++;
		$this->monthlyViewCount++;
	}
	
	/**
	 * @return null|int
	 */
	public function getWeeklyViewCount(): ?int
	{
		return $this->weeklyViewCount;
	}
	
	/**
	 * @return void
	 */
	public function resetWeeklyViewCount(): void
	{
		$this->weeklyViewCount = 0;
	}
	
	/**
	 * @return int
	 */
	public function getMonthlyViewCount(): int
	{
		return $this->monthlyViewCount;
	}
	
	/**
	 * @return void
	 */
	public function resetMonthlyViewCount(): void
	{
		$this->monthlyViewCount = 0;
	}
}