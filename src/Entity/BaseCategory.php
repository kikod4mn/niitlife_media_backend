<?php

namespace App\Entity;

use App\Entity\AbstractEntity\AbstractEntity;

abstract class BaseCategory extends AbstractEntity
{
	/**
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
