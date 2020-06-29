<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ImageCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(
 *     itemOperations={"GET"},
 *
 *     collectionOperations={"GET"}
 * )
 * @ORM\Entity(repositoryClass=ImageCategoryRepository::class)
 */
class ImageCategory extends BaseCategory
{
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Image", mappedBy="category", cascade={"all"})
	 * @var Collection
	 */
	protected Collection $images;
	
	/**
	 * @ORM\OneToOne(targetEntity="App\Entity\Image")
	 * @ORM\JoinColumn(nullable=true)
	 * @var null|Image
	 */
	protected ?Image $leadingImage = null;
	
	/**
	 * CategoryImage constructor.
	 */
	public function __construct()
	{
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
	 * @return Collection
	 */
	public function getImages(): Collection
	{
		return $this->images;
	}
	
	/**
	 * @return null|Image
	 */
	public function getLeadingImage(): ?Image
	{
		if (! $this->leadingImage || null === $this->leadingImage) {
			
			return $this->images[mt_rand(0, count($this->images) - 1)];
		}
		
		return $this->leadingImage;
	}
	
	/**
	 * @param  null|Image  $leadingImage
	 * @return $this|ImageCategory
	 */
	public function setLeadingImage(?Image $leadingImage): ImageCategory
	{
		$this->leadingImage = $leadingImage;
		
		return $this;
	}
}
