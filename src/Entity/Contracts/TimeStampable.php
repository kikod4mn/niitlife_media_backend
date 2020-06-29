<?php

declare(strict_types = 1);

namespace App\Entity\Contracts;

use DateTimeInterface;

interface TimeStampable
{
	/**
	 * @return DateTimeInterface
	 */
	public function getCreatedAt(): ?DateTimeInterface;
	
	/**
	 * @param  DateTimeInterface  $createdAt
	 * @return $this|TimeStampable
	 */
	public function setCreatedAt(DateTimeInterface $createdAt): TimeStampable;
	
	/**
	 * @return DateTimeInterface
	 */
	public function getUpdatedAt(): ?DateTimeInterface;
	
	/**
	 * @param  DateTimeInterface  $updatedAt
	 * @return $this|TimeStampable
	 */
	public function setUpdatedAt(DateTimeInterface $updatedAt): TimeStampable;
	
	/**
	 * Set timestamps on creation.
	 * @return $this|TimeStampable
	 */
	public function setCreationTimestamps(): TimeStampable;
	
	/**
	 * Set updated timestamps
	 * @return $this|TimeStampable
	 */
	public function setUpdatedTimestamps(): TimeStampable;
	
	/**
	 * Determine if the entity is using timestamps.
	 * @return bool
	 */
	public function hasTimestamps(): bool;
}