<?php

namespace App\Entity;

use App\Entity\AbstractEntity\AbstractEntity;
use App\Entity\Concerns\SluggableTrait;
use App\Entity\Contracts\Sluggable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

class Tag extends AbstractEntity implements Sluggable
{
	use SluggableTrait;
	
	const SLUGGABLE_FIELD = 'title';
	
	/**
	 * @Groups({"post:list", "post:read", "tag:read"})
	 * @var null|UuidInterface
	 */
	protected ?UuidInterface $id = null;
	
	/**
	 * @Groups({"post:list", "post:read", "tag:read", "tag:write", "tag:update"})
	 * @var null|string
	 */
	protected ?string $title = null;
	
	/**
	 * @Groups({"post:list", "post:read", "tag:read", "tag:write", "tag:update"})
	 * @var null|string
	 */
	protected ?string $slug = null;
	
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
