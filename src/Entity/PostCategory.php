<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\PostCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ApiResource(
 *     itemOperations={"GET"},
 *
 *     collectionOperations={"GET"}
 * )
 * @ORM\Entity(repositoryClass=PostCategoryRepository::class)
 */
class PostCategory extends BaseCategory
{
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="category", cascade={"all"})
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
