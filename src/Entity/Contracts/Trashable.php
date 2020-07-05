<?php

declare(strict_types = 1);

namespace App\Entity\Contracts;

use DateTimeInterface;

interface Trashable
{
	/**
	 * @param  null|DateTimeInterface  $trashedAt
	 * @return $this|Trashable
	 */
	public function setTrashedAt(?DateTimeInterface $trashedAt): Trashable;
	
	/**
	 * @return null|DateTimeInterface
	 */
	public function getTrashedAt(): ?DateTimeInterface;
	
	/**
	 * @return null|string
	 */
	public function getTrashedAtColumn(): ?string;
	
	/**
	 * Send an entity to trash.
	 * @return $this|Trashable
	 */
	public function trash(): Trashable;
	
	/**
	 * @return $this|Trashable
	 */
	public function restore(): Trashable;
	
	/**
	 * Determine if Entity has been soft deleted.
	 * @return bool
	 */
	public function isTrashed(): bool;
}