<?php

declare(strict_types = 1);

namespace App\Controller\Image;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\JsonNormalizedResponse;
use App\Repository\ImageRepository;
use App\Security\Voter\ImageVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FindByIdController extends AbstractController
{
	use JsonNormalizedMessages, JsonNormalizedResponse;
	
	/**
	 * @var ImageRepository
	 */
	private ImageRepository $imageRepository;
	
	public function __construct(ImageRepository $imageRepository)
	{
		$this->imageRepository = $imageRepository;
	}
	
	public function __invoke(string $id): JsonResponse
	{
		$image = $this->getImageRepository()->find($id);
		
		if (! $image) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf(
					'Image with id "%s" not found',
					$id
				)
			);
		}
		
		$this->denyAccessUnlessGranted(ImageVoter::VIEW, $image);
		
		return $this->jsonNormalized($image, ['image:read']);
	}
	
	public function getImageRepository(): ImageRepository
	{
		return $this->imageRepository;
	}
}