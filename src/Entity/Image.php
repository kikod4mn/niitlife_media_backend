<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\AbstractEntity\AbstractEntity;
use App\Entity\Concerns\CountableLikesTrait;
use App\Entity\Concerns\CountableViewsTrait;
use App\Entity\Concerns\AuthorableTrait;
use App\Entity\Concerns\TimeStampableTrait;
use App\Entity\Concerns\LikableTrait;
use App\Entity\Concerns\PublishableTrait;
use App\Entity\Concerns\SluggableTrait;
use App\Entity\Concerns\UuidableTrait;
use App\Entity\Contracts\Authorable;
use App\Entity\Contracts\Likeable;
use App\Entity\Contracts\Publishable;
use App\Entity\Contracts\Sluggable;
use App\Entity\Contracts\TimeStampable;
use App\Entity\Contracts\Viewable;
use App\Repository\ImageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     itemOperations={"GET"},
 *
 *     collectionOperations={"GET"}
 * )
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 * @UniqueEntity(fields="slug", message="How did this happen???? Slug should be unique!!")
 */
class Image extends AbstractEntity implements Authorable, TimeStampable, Publishable, Sluggable, Likeable, Viewable
{
	use UuidableTrait, AuthorableTrait, TimeStampableTrait, PublishableTrait, SluggableTrait, LikableTrait, CountableLikesTrait, CountableViewsTrait;
	
	public const SLUGGABLE_FIELD = 'title';
	
	/**
	 * @Groups({"get", "get-post-with-comments"})
	 * @var null|string
	 */
	protected ?string $id = null;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=false)
	 * @var null|string
	 */
	protected ?string $title = null;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=false)
	 * @Groups({"get"})
	 * @var null|string
	 */
	protected ?string $slug = null;
	
	/**
	 * @ORM\Column(type="text", nullable=false)
	 * @var null|string
	 */
	protected ?string $description = null;
	
	/**
	 * @ORM\Column(type="text", nullable=false)
	 * @var null|string
	 */
	protected ?string $original = null;
	
	/**
	 * @ORM\Column(type="text", nullable=false)
	 * @var null|string
	 */
	protected ?string $thumbnail = null;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\ImageCategory", inversedBy="images")
	 * @ORM\JoinColumn(nullable=false)
	 * @var null|ImageCategory
	 */
	protected ?ImageCategory $category = null;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="images")
	 * @ORM\JoinColumn(nullable=false)
	 * @ORM\OrderBy({"createdAt" = "DESC"})
	 * @var null|User
	 */
	protected ?User $author = null;
	
	/**
	 * @ORM\OneToMany(targetEntity="ImageComment", mappedBy="image")
	 * @ORM\JoinColumn(nullable=false)
	 * @var Collection
	 */
	protected Collection $comments;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="imagesLiked", cascade={"all"})
	 * @ORM\JoinTable(name="image_likes",
	 *     joinColumns={
	 *          @ORM\JoinColumn(name="image_id", referencedColumnName="id", nullable=false)
	 *     },
	 *     inverseJoinColumns={
	 *          @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
	 *     }
	 * )
	 * @var Collection
	 */
	protected Collection $likedBy;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Tag", inversedBy="images", cascade={"all"})
	 * @ORM\JoinTable(name="image_tags",
	 *     joinColumns={
	 *          @ORM\JoinColumn(name="image_id", referencedColumnName="id", nullable=false)
	 *     },
	 *     inverseJoinColumns={
	 *          @ORM\JoinColumn(name="tag_id", referencedColumnName="id", nullable=false)
	 *     }
	 * )
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
	
}
