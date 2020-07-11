<?php

declare(strict_types = 1);

namespace App\Security\Contracts;

interface VotableConstants
{
	/**
	 * @var string
	 */
	const VIEW = 'VIEW';
	
	/**
	 * @var string
	 */
	const EDIT = 'EDIT';
	
	/**
	 * @var string
	 */
	const CREATE = 'CREATE';
	
	/**
	 * @var string
	 */
	const PUBLISH = 'PUBLISH';
	
	/**
	 * @var string
	 */
	const TRASH = 'TRASH';
	
	/**
	 * @var string
	 */
	const RESTORE = 'RESTORE';
	
	/**
	 * @var string
	 */
	const DELETE = 'DELETE';
}