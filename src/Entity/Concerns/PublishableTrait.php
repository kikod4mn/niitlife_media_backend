<?php

declare(strict_types = 1);

namespace App\Entity\Concerns;

use App\Entity\Contracts\Publishable;
use DateTimeInterface;

trait PublishableTrait
{
	/**
	 * @return bool
	 */
	public function isPublished(): ?bool
	{
		return ! is_null($this->{$this->getPublishedAtColumn()});
	}
	
	/**
	 * @return null|DateTimeInterface
	 */
	public function getPublishedAt(): ?DateTimeInterface
	{
		return $this->{$this->getPublishedAtColumn()};
	}
	
	/**
	 * @param  null|DateTimeInterface  $publishedAt
	 * @return $this|Publishable
	 */
	public function setPublishedAt(?DateTimeInterface $publishedAt): Publishable
	{
		$this->{$this->getPublishedAtColumn()} = $publishedAt;
		
		return $this;
	}
	
	/**
	 * @return null|string
	 */
	public function getPublishedAtColumn(): ?string
	{
		return defined('static::PUBLISHED_AT') ? static::PUBLISHED_AT : 'publishedAt';
	}
	
	/**
	 * Publish an entity.
	 * @return $this|Publishable
	 */
	public function publish(): Publishable
	{
		$this->setPublishedAt($this->freshTimestamp());
		
		return $this;
	}
	
	/**
	 * Un-publish an entity.
	 * @return $this|Publishable
	 */
	public function unPublish(): Publishable
	{
		$this->setPublishedAt(null);
		
		return $this;
	}
}