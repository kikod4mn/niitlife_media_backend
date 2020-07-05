<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class PostCategory extends BaseCategory
{
	/**
	 * @var Collection
	 */
	protected Collection $posts;
	
	/**
	 * CategoryImage constructor.
	 */
	public function __construct()
	{
		$this->posts = new ArrayCollection();
	}
	
	/**
	 * @return array
	 */
	public function jsonSerialize(): array
	{
		return [
			'id' => $this->getId(),
		];
	}
	
	/**
	 * @return null|Collection
	 */
	public function getPosts(): ?Collection
	{
		return $this->posts;
	}
}
