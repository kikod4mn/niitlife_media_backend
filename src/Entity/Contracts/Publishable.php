<?php

declare(strict_types = 1);

namespace App\Entity\Contracts;

use DateTimeInterface;

interface Publishable
{
	/**
	 * @return null|bool
	 */
	public function isPublished(): ?bool;
	
	/**
	 * @return Publishable
	 */
	public function publish(): Publishable;
	
	/**
	 * @return Publishable
	 */
	public function unPublish(): Publishable;
	
	/**
	 * @param  DateTimeInterface  $publishedAt
	 * @return $this|Publishable
	 */
	public function setPublishedAt(DateTimeInterface $publishedAt): Publishable;
	
	/**
	 * @return null|DateTimeInterface
	 */
	public function getPublishedAt(): ?DateTimeInterface;
}