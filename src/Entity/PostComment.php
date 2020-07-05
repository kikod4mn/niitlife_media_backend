<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class PostComment extends BaseComment
{
	/**
	 * @var null|User
	 */
	protected ?User $author = null;
	
	/**
	 * @var null|Post
	 */
	protected ?Post $post = null;
	
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
	 * @return null|Post
	 */
	public function getPost(): ?Post
	{
		return $this->post;
	}
	
	/**
	 * @param  Post  $post
	 * @return $this|PostComment
	 */
	public function setPost(Post $post): PostComment
	{
		$this->post = $post;
		
		return $this;
	}
	
	/**
	 * @return Collection
	 */
	public function getLikedBy(): Collection
	{
		return $this->likedBy;
	}
}
