<?php

namespace App\Entity;

use App\Entity\AbstractEntity\AbstractEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Tag extends AbstractEntity
{
	/**
	 * @var null|string
	 */
	protected ?string $title = null;
	
	/**
	 * @var Collection
	 */
	protected Collection $posts;
	
	/**
	 * @var Collection
	 */
	protected Collection $images;
	
	/**
	 * Tag constructor.
	 */
	public function __construct()
	{
		$this->posts  = new ArrayCollection();
		$this->images = new ArrayCollection();
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
	 * @return null|string
	 */
	public function getTitle(): ?string
	{
		return $this->title;
	}
	
	/**
	 * @param  string  $title
	 * @return $this|Tag
	 */
	public function setTitle(string $title): Tag
	{
		$this->title = $title;
		
		return $this;
	}
	
	/**
	 * @return Collection
	 */
	public function getPosts(): Collection
	{
		return $this->posts;
	}
	
	/**
	 * @return Collection
	 */
	public function getImages(): Collection
	{
		return $this->images;
	}
}
