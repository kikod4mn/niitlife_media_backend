<?php

namespace App\Entity;

use App\Entity\Concerns\SluggableTrait;
use App\Entity\Contracts\Sluggable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

class PostCategory extends BaseCategory implements Sluggable
{
	use SluggableTrait;
	
	/**
	 * @Groups({"post:list", "post:read", "postCategory:list", "postCategory:read"})
	 * @var null|UuidInterface
	 */
	protected ?UuidInterface $id = null;
	
	/**
	 * @Groups({
	 *     "post:list", "post:read", "postCategory:list", "postCategory:read",
	 *     "postCategory:write", "postCategory:update"
	 * })
	 * @var null|string
	 */
	protected ?string $title = null;
	
	/**
	 * @Groups({
	 *     "post:list", "post:read", "postCategory:list", "postCategory:read",
	 *     "postCategory:write", "postCategory:update"
	 * })
	 * @var null|string
	 */
	protected ?string $slug = null;
	
	/**
	 * @Groups({"postCategory:read"})
	 * @var Collection
	 */
	protected Collection $posts;
	
	/**
	 * CategoryImage constructor.
	 */
	public function __construct()
	{
		$this->posts = new ArrayCollection();
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
	 * @return null|Collection
	 */
	public function getPosts(): ?Collection
	{
		return $this->posts;
	}
}
