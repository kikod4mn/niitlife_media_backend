<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ImageComment extends BaseComment
{
	/**
	 * @var null|UuidInterface
	 */
	protected ?UuidInterface $id = null;
	
	/**
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
