<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ImageCategory extends BaseCategory
{
	/**
	 * @var Collection
	 */
	protected Collection $images;
	
	/**
	 * @var null|Image
	 */
	protected ?Image $leadingImage = null;
	
	/**
	 * CategoryImage constructor.
	 */
	public function __construct()
	{
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
	 * @return Collection
	 */
	public function getImages(): Collection
	{
		return $this->images;
	}
	
	/**
	 * @return null|Image
	 */
	public function getLeadingImage(): ?Image
	{
		if (! $this->leadingImage || null === $this->leadingImage) {
			
			return $this->images[mt_rand(0, count($this->images) - 1)];
		}
		
		return $this->leadingImage;
	}
	
	/**
	 * @param  null|Image  $leadingImage
	 * @return $this|ImageCategory
	 */
	public function setLeadingImage(?Image $leadingImage): ImageCategory
	{
		$this->leadingImage = $leadingImage;
		
		return $this;
	}
}
