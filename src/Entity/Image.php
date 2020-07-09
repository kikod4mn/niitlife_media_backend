<?php

namespace App\Entity;

use App\Entity\AbstractEntity\AbstractEntity;
use App\Entity\Concerns\CountableLikesTrait;
use App\Entity\Concerns\CountableViewsTrait;
use App\Entity\Concerns\AuthorableTrait;
use App\Entity\Concerns\TimeStampableTrait;
use App\Entity\Concerns\LikableTrait;
use App\Entity\Concerns\PublishableTrait;
use App\Entity\Concerns\SluggableTrait;
use App\Entity\Concerns\TrashableTrait;
use App\Entity\Contracts\Authorable;
use App\Entity\Contracts\Likeable;
use App\Entity\Contracts\Publishable;
use App\Entity\Contracts\Sluggable;
use App\Entity\Contracts\TimeStampable;
use App\Entity\Contracts\Trashable;
use App\Entity\Contracts\Viewable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Image extends AbstractEntity implements Authorable, TimeStampable, Publishable, Sluggable, Likeable, Viewable, Trashable
{
	use AuthorableTrait, TimeStampableTrait, PublishableTrait, SluggableTrait, LikableTrait, CountableLikesTrait, CountableViewsTrait, TrashableTrait;
	
	public const SLUGGABLE_FIELD = 'title';
	
	/**
	 * @var null|string
	 */
	protected ?string $title = null;
	
	/**
	 * @var null|string
	 */
	protected ?string $description = null;
	
	/**
	 * @var null|string
	 */
	protected ?string $original = null;
	
	/**
	 * @var null|string
	 */
	protected ?string $half = null;
	
	/**
	 * @var null|string
	 */
	protected ?string $thumbnail = null;
	
	/**
	 * @var null|ImageCategory
	 */
	protected ?ImageCategory $category = null;
	
	/**
	 * @var null|User
	 */
	protected ?User $author = null;
	
	/**
	 * @var Collection
	 */
	protected Collection $comments;
	
	/**
	 * @var Collection
	 */
	protected Collection $likedBy;
	
	/**
	 * @var Collection
	 */
	protected Collection $tags;
	
	/**
	 * Image constructor.
	 */
	public function __construct()
	{
		$this->publishedAt = $this->freshTimestamp();
		$this->comments    = new ArrayCollection();
		$this->likedBy     = new ArrayCollection();
		$this->tags        = new ArrayCollection();
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
	 * @return $this|Image
	 */
	public function setTitle(string $title): Image
	{
		$this->title = $title;
		
		return $this;
	}
	
	/**
	 * @return null|string
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}
	
	/**
	 * @param  string  $description
	 * @return $this|Image
	 */
	public function setDescription(string $description): Image
	{
		$this->description = $description;
		
		return $this;
	}
	
	/**
	 * @return null|string
	 */
	public function getOriginal(): ?string
	{
		return $this->original;
	}
	
	/**
	 * @param  string  $original
	 * @return $this|Image
	 */
	public function setOriginal(string $original): Image
	{
		$this->original = $original;
		
		return $this;
	}
	
	/**
	 * @param  string  $half
	 * @return $this|Image
	 */
	public function setHalf(string $half): Image
	{
		$this->half = $half;
		
		return $this;
	}
	
	/**
	 * @return null|string
	 */
	public function getHalf(): ?string
	{
		return $this->half;
	}
	
	/**
	 * @return null|string
	 */
	public function getThumbnail(): ?string
	{
		return $this->thumbnail;
	}
	
	/**
	 * @param  string  $thumbnail
	 * @return $this|Image
	 */
	public function setThumbnail(string $thumbnail): Image
	{
		$this->thumbnail = $thumbnail;
		
		return $this;
	}
	
	/**
	 * @return null|ImageCategory
	 */
	public function getCategory(): ?ImageCategory
	{
		return $this->category;
	}
	
	/**
	 * @param  ImageCategory  $category
	 * @return $this|Image
	 */
	public function setCategory(ImageCategory $category): Image
	{
		$this->category = $category;
		
		return $this;
	}
	
	/**
	 * @return Collection
	 */
	public function getComments(): Collection
	{
		return $this->comments;
	}
	
	/**
	 * @return null|Collection
	 */
	public function getTags(): ?Collection
	{
		return $this->tags;
	}
	
	/**
	 * @param  array  $tags
	 * @return Image
	 */
	public function setTags(array $tags): self
	{
		$this->tags = new ArrayCollection();
		
		foreach ($tags as $tag) {
			
			$this->addTag($tag);
		}
		
		return $this;
	}
	
	/**
	 * @param  Tag  $tag
	 * @return $this|Image
	 */
	public function addTag(Tag $tag): Image
	{
		if (! $this->tags->contains($tag)) {
			$this->tags->add($tag);
		}
		
		return $this;
	}
	
	/**
	 * @param  Tag  $tag
	 * @return $this|Image
	 */
	public function removeTag(Tag $tag): Image
	{
		if ($this->tags->contains($tag)) {
			$this->tags->remove($tag);
		}
		
		return $this;
	}
}
