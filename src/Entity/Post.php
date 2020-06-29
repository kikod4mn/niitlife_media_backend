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
use App\Entity\Contracts\Authorable;
use App\Entity\Contracts\Likeable;
use App\Entity\Contracts\Publishable;
use App\Entity\Contracts\Sluggable;
use App\Entity\Contracts\TimeStampable;
use App\Entity\Contracts\Viewable;
use App\Repository\PostRepository;
use App\Support\Str;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 * @UniqueEntity(fields="slug", message="How did this happen???? Slug should be unique!!")
 */
class Post extends AbstractEntity implements Authorable, Sluggable, Publishable, TimeStampable, Viewable, Likeable
{
	use AuthorableTrait, PublishableTrait, SluggableTrait, TimeStampableTrait, CountableViewsTrait, LikableTrait, CountableLikesTrait;
	
	public const SLUGGABLE_FIELD = 'title';
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=false)
	 * @Assert\NotBlank()
	 * @Assert\Length(min="10", minMessage="Minimum of 10 characters required for the 'Title'.", max="255", maxMessage="Maximum length of 255 characters for title.")
	 * @Groups({"get", "post", "put", "get-post-with-comments"})
	 * @var null|string
	 */
	protected ?string $title = null;
	
	/**
	 * @ORM\Column(type="text", length=4294967295, nullable=false)
	 * @Assert\NotBlank()
	 * @Assert\Length(min="20", minMessage="Minimum of 20 characters required for post 'Body'.")
	 * @Groups({"get", "post", "put", "get-post-with-comments"})
	 * @var null|string
	 */
	protected ?string $body = null;
	
	/**
	 * @ORM\Column(type="string", length=200, nullable=false)
	 * @Groups({"get-post-with-comments"})
	 * @var null|string
	 */
	protected ?string $snippet = null;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\PostCategory", inversedBy="posts")
	 * @ORM\JoinColumn(nullable=false)
	 * @Assert\NotBlank()
	 * @Groups({"get", "post", "put", "get-post-with-comments"})
	 * @var null|PostCategory
	 */
	protected ?PostCategory $category = null;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="posts")
	 * @ORM\JoinColumn(nullable=false)
	 * @ORM\OrderBy({"createdAt" = "DESC"})
	 * @Groups({"get-post-with-comments"})
	 * @var null|User
	 */
	protected ?User $author = null;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\PostComment", mappedBy="post")
	 * @Groups({"get-post-with-comments"})
	 * @var Collection
	 */
	protected Collection $comments;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="postsLiked", cascade={"all"})
	 * @ORM\JoinTable(name="post_likes",
	 *     joinColumns={
	 *          @ORM\JoinColumn(name="post_id", referencedColumnName="id", nullable=false)
	 *     },
	 *     inverseJoinColumns={
	 *          @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
	 *     }
	 * )
	 * @Groups({"get-post-with-comments"})
	 * @var Collection
	 */
	protected Collection $likedBy;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Tag", inversedBy="posts", cascade={"all"})
	 * @ORM\JoinTable(name="post_tags",
	 *     joinColumns={
	 *          @ORM\JoinColumn(name="post_id", referencedColumnName="id", nullable=false)
	 *     },
	 *     inverseJoinColumns={
	 *          @ORM\JoinColumn(name="tag_id", referencedColumnName="id", nullable=false)
	 *     }
	 * )
	 * @Groups({"get-post-with-comments"})
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
	 * @throws Exception
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
