<?php

namespace App\Entity;

use App\Entity\AbstractEntity\AbstractEntity;

abstract class BaseCategory extends AbstractEntity
{
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
