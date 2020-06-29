<?php

declare(strict_types = 1);

namespace App\Entity\Contracts;

interface Sluggable
{
	/**
	 * @param  null|string  $sluggable
	 * @return $this|Sluggable
	 */
	public function setSlug(string $sluggable = null): Sluggable;
	
	/**
	 * @return null|string
	 */
	public function getSlug(): ?string;
}