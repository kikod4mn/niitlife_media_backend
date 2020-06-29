<?php

namespace App\Entity;

use App\Entity\AbstractEntity\AbstractEntity;
use App\Entity\Concerns\CountableLikesTrait;
use App\Entity\Concerns\FilterProfanitiesTrait;
use App\Entity\Concerns\AuthorableTrait;
use App\Entity\Concerns\TimeStampableTrait;
use App\Entity\Concerns\LikableTrait;
use App\Entity\Concerns\PublishableTrait;
use App\Entity\Contracts\Authorable;
use App\Entity\Contracts\Likeable;
use App\Entity\Contracts\Publishable;
use App\Entity\Contracts\TimeStampable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "blog_post_comment" = "App\Model\CommentBlogPost",
 *     "image_comment" = "App\Model\CommentImage"
 * })
 * @ORM\MappedSuperclass()
 */
abstract class BaseComment extends AbstractEntity implements Authorable, TimeStampable, Publishable, Likeable
{
	use AuthorableTrait, TimeStampableTrait, PublishableTrait, LikableTrait, CountableLikesTrait, FilterProfanitiesTrait;
	
	/**
	 * @ORM\Column(type="text", length=4000, nullable=false)
	 * @Assert\NotBlank()
	 * @Assert\Length(min="10", minMessage="Minimum of 10 characters is required for the 'body'.", max="4000", maxMessage="400 chars is max for a comment 'body'. Cool it Shakespeare!")
	 * @Groups({"get", "post", "put", "get-post-with-comments"})
	 * @var null|string
	 */
	protected ?string $body = null;
	
	/**
	 * @return null|string
	 */
	public function getBody(): ?string
	{
		return $this->body;
	}
	
	/**
	 * @param  string  $body
	 * @return $this|BaseComment
	 */
	public function setBody(string $body): BaseComment
	{
		$this->body = $this->cleanString($body);
		
		return $this;
	}
}
