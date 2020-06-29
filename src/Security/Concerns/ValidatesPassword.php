<?php

declare(strict_types = 1);

namespace App\Security\Concerns;

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

trait ValidatesPassword
{
	/**
	 * @param  string  $password
	 * @return mixed
	 */
	protected function validateRawPassword(string $password)
	{
		$validator = Validation::createValidator();
		
		return $validator->validate(
			$password,
			new Collection(
				[
					new NotBlank(['message' => 'Password cannot be blank']),
					new Length(['min' => 8, 'minMessage' => 'At least 8 characters for the password are required.']),
				]
			)
		);
	}
}