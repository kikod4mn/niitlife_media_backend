<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ImageComment extends BaseComment
{
	/**
	 * @var null|User
	 */
	protected ?User $author = null;
	
	/**
	 * @var null|Image
	 */
	protected ?Image $image = null;
	
	/**
	 * @var Collection
	 */
	protected Collection $likedBy;
	
	/**
	 * Comment constructor.
	 */
	public function __construct()
	{
		$this->publishedAt = $this->freshTimestamp();
		$this->likedBy     = new ArrayCollection();
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
	 * @return null|Image
	 */
	public function getImage(): ?Image
	{
		return $this->image;
	}
	
	/**
	 * @param  Image  $image
	 * @return $this|ImageComment
	 */
	public function setImage(Image $image): ImageComment
	{
		$this->image = $image;
		
		return $this;
	}
}
