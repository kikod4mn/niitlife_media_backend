<?php

declare(strict_types = 1);

namespace App\Entity\Concerns;

use App\Entity\Contracts\Trashable;
use DateTimeInterface;

trait TrashableTrait
{
	/**
	 * @return null|DateTimeInterface
	 */
	public function getTrashedAt(): ?DateTimeInterface
	{
		return $this->{$this->getTrashedAtColumn()};
	}
	
	/**
	 * @param  null|DateTimeInterface  $trashedAt
	 * @return $this|Trashable
	 */
	public function setTrashedAt(?DateTimeInterface $trashedAt): Trashable
	{
		$this->{$this->getTrashedAtColumn()} = $trashedAt;
		
		return $this;
	}
	
	/**
	 * @return null|string
	 */
	public function getTrashedAtColumn(): ?string
	{
		return defined('static::TRASHED_AT') ? static::TRASHED_AT : 'trashedAt';
	}
	
	public function isTrashed(): bool
	{
		return ! is_null($this->{$this->getTrashedAtColumn()});
	}
	
	/**
	 * Send an entity to trash.
	 * @return $this|Trashable
	 */
	public function trash(): Trashable
	{
		$this->setTrashedAt($this->freshTimestamp());
		
		return $this;
	}
	
	/**
	 * @return $this|Trashable
	 */
	public function restore(): Trashable
	{
		$this->setTrashedAt(null);
		
		return $this;
	}
}