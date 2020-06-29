<?php

declare(strict_types = 1);

namespace App\Entity\Concerns;

use App\Entity\Contracts\Sluggable;
use App\Support\Str;
use Exception;

trait SluggableTrait
{
	/**
	 * First makes sure the slug passed in is not too long.
	 * If null is passed, assumes the presence of "title" field.
	 * If title does not exist, assumes presence of "static::SLUGGABLE_FIELD".
	 * If neither is present on the entity and no slug is passed in, sets the slug to a string of 'null-slug' and appends a pseudo random bit of gibberish.
	 * @param  null|string  $sluggable
	 * @return $this|Sluggable
	 * @throws Exception
	 */
	public function setSlug(string $sluggable = null): Sluggable
	{
		// First check if there is a value passed in. If there is, check the string and then generate slug.
		if (! is_null($sluggable)) {
			// If slug is over limit, cut the end.
			if ($this->shouldLimit($sluggable)) {
				
				$sluggable = $this->limitSlug($sluggable);
			}
			
			$this->slug = Str::slug($sluggable);
			
			return $this;
		}
		
		// Second, check if a property of title exists on the entity and try to make a slug with it.
		if (property_exists($this, 'title')) {
			
			// If slug is over limit, cut the end.
			if ($this->shouldLimit($this->title)) {
				
				$sluggable = $this->limitSlug($this->title);
			}
			
			$this->slug = Str::slug(isset($sluggable) ? $sluggable : $this->title);
			
			return $this;
		}
		
		if (defined('static::SLUGGABLE_FIELD')) {
			
			$sluggable = $this->{static::SLUGGABLE_FIELD};
			
			// If slug is over limit, cut the end.
			if ($this->shouldLimit($sluggable)) {
				
				$sluggable = $this->limitSlug($sluggable);
			}
			
			$this->slug = Str::slug($sluggable);
			
			return $this;
		}
		
		$this->slug = 'null-slug-' . Str::random(34);
		
		return $this;
	}
	
	/**
	 * @return null|string
	 */
	public function getSlug(): ?string
	{
		return $this->slug;
	}
	
	/**
	 * @param  string  $sluggable
	 * @return bool
	 */
	protected function shouldLimit(string $sluggable): bool
	{
		return Str::length($sluggable) > 50;
	}
	
	/**
	 * @param  string  $sluggable
	 * @return string
	 */
	protected function limitSlug(string $sluggable): string
	{
		return Str::limit($sluggable, 50, '');
	}
}