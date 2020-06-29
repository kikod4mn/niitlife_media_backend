<?php

namespace App\Entity;

use App\Entity\AbstractEntity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "image_category" = "App\Model\ImageCategory",
 *     "post_category" = "App\Model\PostCategory"
 * })
 * @ORM\MappedSuperclass()
 */
abstract class BaseCategory extends AbstractEntity
{
	/**
	 * @ORM\Column(type="string", length=100, nullable=false)
	 * @var null|string
	 */
	protected ?string $title = null;
	
	/**
	 * @return null|string
	 */
	public function getTitle(): ?string
	{
		return $this->title;
	}
	
	/**
	 * @param  string  $title
	 * @return $this|BaseCategory
	 */
	public function setTitle(string $title): BaseCategory
	{
		$this->title = $title;
		
		return $this;
	}
}
