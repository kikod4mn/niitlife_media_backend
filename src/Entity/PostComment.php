<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class PostComment extends BaseComment
{
	/**
	 * @Groups({"comment:read", "post:read"})
	 * @var null|UuidInterface
	 */
	protected ?UuidInterface $id = null;
	
	/**
	 * @Groups({"comment:write", "comment:read", "comment:update", "post:read"})
	 * @Assert\NotBlank(message="Body cannot be blank.")
	 * @Assert\Length(
	 *     min="15",
	 *     minMessage="Body must be at least {{ limit }} characters long.",
	 *     max="4000",
	 *     maxMessage="Body cannot exceed {{ limit }} characters."
	 * )
	 * @var null|string
	 */
	protected ?string $body = null;
	
	/**
	 * @Groups({"post:read", "comment:read"})
	 * @var null|User
	 */
	protected ?User $author = null;
	
	/**
	 * @Groups({"comment:write"})
	 * @var null|Post
	 */
	protected ?Post $post = null;
	
	/**
	 * @Groups({"comment:read"})
	 * @var Collection
	 */
	protected Collection $likedBy;
	
	/**
	 * @Groups({"comment:read"})
	 * @var int
	 */
	protected int $likeCount = 0;
	
	/**
	 * @var int
	 */
	protected int $weeklyLikeCount = 0;
	
	/**
	 * @var null|DateTimeInterface
	 */
	protected ?DateTimeInterface $trashedAt = null;
	
	/**
	 * @var DateTimeInterface
	 */
	protected ?DateTimeInterface $publishedAt = null;
	
	/**
	 * @Groups({"post:read", "comment:read"})
	 * @var null|DateTimeInterface
	 */
	protected ?DateTimeInterface $createdAt = null;
	
	/**
	 * @var null|DateTimeInterface
	 */
	protected ?DateTimeInterface $updatedAt = null;
	
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
