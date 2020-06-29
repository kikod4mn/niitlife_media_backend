<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\AbstractEntity\AbstractEntity;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TagRepository::class)
 */
class Tag extends AbstractEntity
{
	/**
	 * @ORM\Column(type="string", length=200, nullable=false)
	 * @Groups({"get-post-with-comments"})
	 * @var null|string
	 */
	protected ?string $title = null;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Post", mappedBy="tags", cascade={"all"})
	 * @var Collection
	 */
	protected Collection $posts;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Image", mappedBy="tags", cascade={"all"})
	 * @var Collection
	 */
	protected Collection $images;
	
	/**
	 * Tag constructor.
	 */
	public function __construct()
	{
		$this->posts  = new ArrayCollection();
		$this->images = new ArrayCollection();
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
	 * @return $this|Tag
	 */
	public function setTitle(string $title): Tag
	{
		$this->title = $title;
		
		return $this;
	}
	
	/**
	 * @return Collection
	 */
	public function getPosts(): Collection
	{
		return $this->posts;
	}
	
	/**
	 * @return Collection
	 */
	public function getImages(): Collection
	{
		return $this->images;
	}
}
