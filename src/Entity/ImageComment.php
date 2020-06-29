<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ImageCommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(
 *     itemOperations={"GET"},
 *
 *     collectionOperations={"GET"}
 * )
 * @ORM\Entity(repositoryClass=ImageCommentRepository::class)
 */
class ImageComment extends BaseComment
{
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments", fetch="EAGER")
	 * @ORM\JoinColumn(nullable=false)
	 * @ORM\OrderBy({"createdAt" = "DESC"})
	 * @var null|User
	 */
	protected ?User $author = null;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Image", inversedBy="comments")
	 * @ORM\JoinColumn(nullable=false)
	 * @ORM\OrderBy({"createdAt" = "DESC"})
	 * @var null|Image
	 */
	protected ?Image $image = null;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="commentsLiked", cascade={"all"})
	 * @ORM\JoinTable(name="image_comment_likes",
	 *     joinColumns={
	 *          @ORM\JoinColumn(name="comment_id", referencedColumnName="id", nullable=false)
	 *     },
	 *     inverseJoinColumns={
	 *          @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
	 *     }
	 * )
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
