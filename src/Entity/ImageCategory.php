<?php

namespace App\Entity;

use App\Entity\Concerns\SluggableTrait;
use App\Entity\Contracts\Sluggable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class ImageCategory extends BaseCategory implements Sluggable
{
	use SluggableTrait;
	
	/**
	 * @var null|UuidInterface
	 */
	protected ?UuidInterface $id = null;
	
	/**
	 * @Groups({
	 *     "image:list", "image:read", "imageCategory:list", "imageCategory:read",
	 *     "imageCategory:write", "imageCategory:update"
	 * })
	 * @Assert\NotBlank()
	 * @Assert\Length(
	 *     min="3",
	 *     minMessage="Title must be at least {{ limit }} characters long.",
	 *     max="250",
	 *     maxMessage="Title must be a maximum {{ limit }} characters long."
	 * )
	 * @var null|string
	 */
	protected ?string $title = null;
	
	/**
	 * @Groups({
	 *     "image:list", "image:read", "imageCategory:list", "imageCategory:read",
	 *     "imageCategory:write", "imageCategory:update"
	 * })
	 * @var null|string
	 */
	protected ?string $slug = null;
	
	/**
	 * @var Collection
	 */
	protected Collection $images;
	
	/**
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
