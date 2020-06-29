<?php

namespace App\Entity;

use App\Repository\PostCommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PostCommentRepository::class)
 */
class PostComment extends BaseComment
{
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments", fetch="EAGER")
	 * @ORM\JoinColumn(nullable=false)
	 * @ORM\OrderBy({"createdAt" = "DESC"})
	 * @Groups({"get", "get-post-with-comments"})
	 * @var null|User
	 */
	protected ?User $author = null;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Post", inversedBy="comments")
	 * @ORM\JoinColumn(nullable=false)
	 * @ORM\OrderBy({"createdAt" = "DESC"})
	 * @Groups({"post", "get"})
	 * @var null|Post
	 */
	protected ?Post $post = null;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="commentsLiked", cascade={"all"})
	 * @ORM\JoinTable(name="post_comment_likes",
	 *     joinColumns={
	 *          @ORM\JoinColumn(name="comment_id", referencedColumnName="id", nullable=false)
	 *     },
	 *     inverseJoinColumns={
	 *          @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
	 *     }
	 * )
	 * @Groups({"get", "get-post-with-comments"})
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
