<?php

namespace App\Entity;

use App\Entity\AbstractEntity\AbstractEntity;
use App\Entity\Concerns\CountableLikesTrait;
use App\Entity\Concerns\FilterProfanitiesTrait;
use App\Entity\Concerns\AuthorableTrait;
use App\Entity\Concerns\TimeStampableTrait;
use App\Entity\Concerns\LikableTrait;
use App\Entity\Concerns\PublishableTrait;
use App\Entity\Concerns\TrashableTrait;
use App\Entity\Contracts\Authorable;
use App\Entity\Contracts\Likeable;
use App\Entity\Contracts\Publishable;
use App\Entity\Contracts\TimeStampable;
use App\Entity\Contracts\Trashable;

abstract class BaseComment extends AbstractEntity implements Authorable, TimeStampable, Publishable, Likeable, Trashable
{
	use AuthorableTrait, TimeStampableTrait, PublishableTrait, LikableTrait, CountableLikesTrait, FilterProfanitiesTrait, TrashableTrait;
	
	/**
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
