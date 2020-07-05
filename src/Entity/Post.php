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
use App\Support\Str;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Symfony\Component\Serializer\Annotation\Groups;

class Post extends AbstractEntity implements Authorable, Sluggable, Publishable, TimeStampable, Viewable, Likeable, Trashable
{
	use AuthorableTrait, PublishableTrait, SluggableTrait, TimeStampableTrait, CountableViewsTrait, LikableTrait, CountableLikesTrait, TrashableTrait;
	
	public const SLUGGABLE_FIELD = 'title';
	
	/**
	 * @Groups({"post:list"})
	 * @var null|string
	 */
	protected ?string $title = null;
	
	/**
	 * @Groups({"post:list"})
	 * @var null|string
	 */
	protected ?string $body = null;
	
	/**
	 * @var null|string
	 */
	protected ?string $snippet = null;
	
	/**
	 * @var null|PostCategory
	 */
	protected ?PostCategory $category = null;
	
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
	 * Post constructor.
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
	 * @return $this|Post
	 */
	public function setTitle(string $title): Post
	{
		$this->title = $title;
		
		return $this;
	}
	
	/**
	 * @return null|string
	 */
	public function getBody(): ?string
	{
		return $this->body;
	}
	
	/**
	 * @param  string  $body
	 * @return $this|Post
	 */
	public function setBody(string $body): Post
	{
		$this->body = $body;
		
		$this->setSnippet($body);
		
		return $this;
	}
	
	/**
	 * @return null|string
	 */
	public function getSnippet(): ?string
	{
		return $this->snippet;
	}
	
	/**
	 * @param  string  $snippet
	 * @return $this|Post
	 */
	public function setSnippet(string $snippet): Post
	{
		$this->snippet = Str::limit($snippet, 190);
		
		return $this;
	}
	
	/**
	 * @return null|PostCategory
	 */
	public function getCategory(): ?PostCategory
	{
		return $this->category;
	}
	
	/**
	 * @param  PostCategory  $category
	 * @return $this|Post
	 */
	public function setCategory(PostCategory $category): Post
	{
		$this->category = $category;
		
		return $this;
	}
	
	/**
	 * @return null|Collection
	 */
	public function getComments(): ?Collection
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
	 * @return Post
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
	 * @return $this|Post
	 */
	public function addTag(Tag $tag): Post
	{
		if (! $this->tags->contains($tag)) {
			$this->tags->add($tag);
		}
		
		return $this;
	}
	
	/**
	 * @param  Tag  $tag
	 * @return $this|Post
	 */
	public function removeTag(Tag $tag): Post
	{
		if ($this->tags->contains($tag)) {
			$this->tags->remove($tag);
		}
		
		return $this;
	}
}
