<?php

declare(strict_types = 1);

namespace App\Entity\Concerns;

use App\Entity\Contracts\TimeStampable;
use Carbon\Carbon;
use DateTime;
use DateTimeInterface;

trait TimeStampableTrait
{
	/**
	 * Set usage of timestamps on the entity.
	 * @var bool
	 */
	protected bool $timestamps = true;
	
	/**
	 * @return string
	 */
	public function getCreatedAtColumn(): string
	{
		return defined('static::CREATED_AT') ? static::CREATED_AT : 'createdAt';
	}
	
	/**
	 * @return string
	 */
	public function getUpdatedAtColumn(): string
	{
		return defined('static::UPDATED_AT') ? static::UPDATED_AT : 'updatedAt';
	}
	
	/**
	 * @return DateTimeInterface
	 */
	public function getCreatedAt(): ?DateTimeInterface
	{
		return $this->{$this->getCreatedAtColumn()};
	}
	
	/**
	 * @param  DateTimeInterface  $createdAt
	 * @return $this|TimeStampable
	 */
	public function setCreatedAt(DateTimeInterface $createdAt): TimeStampable
	{
		$this->{$this->getCreatedAtColumn()} = $createdAt;
		
		return $this;
	}
	
	/**
	 * @return DateTimeInterface
	 */
	public function getUpdatedAt(): ?DateTimeInterface
	{
		return $this->{$this->getUpdatedAtColumn()};
	}
	
	/**
	 * @param  DateTimeInterface  $updatedAt
	 * @return $this|TimeStampable
	 */
	public function setUpdatedAt(DateTimeInterface $updatedAt): TimeStampable
	{
		$this->{$this->getUpdatedAtColumn()} = $updatedAt;
		
		return $this;
	}
	
	/**
	 * @return $this|TimeStampable
	 */
	public function setCreationTimestamps(): TimeStampable
	{
		$this->setCreatedAt($this->freshTimestamp());
		
		return $this;
	}
	
	/**
	 * @return $this|TimeStampable
	 */
	public function setUpdatedTimestamps(): TimeStampable
	{
		$this->setUpdatedAt($this->freshTimestamp());
		
		return $this;
	}
	
	/**
	 * Determine if the entity is using timestamps.
	 * @return bool
	 */
	public function hasTimestamps(): bool
	{
		return $this->timestamps;
	}
	
	/**
	 * @param  DateTimeInterface  $dateTime
	 * @return string
	 */
	protected function serializeDate(DateTimeInterface $dateTime): string
	{
		return Carbon::instance($dateTime)->toJSON();
	}
	
	/**
	 * getCreatedAt and getUpdatedAt are already included by default.
	 * @return array
	 */
	protected function getDates()
	{
		$defaults = [
			static::CREATED_AT,
			static::UPDATED_AT,
		];
		
		return $this->hasTimestamps()
			? array_merge($this->dates, $defaults)
			: $this->dates;
	}
	
	/**
	 * @return DateTimeInterface
	 */
	protected function freshTimestamp(): DateTimeInterface
	{
		return new DateTime('now');
	}
}