<?php

declare(strict_types = 1);

namespace App\Controller\PostCategory;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\JsonNormalizedResponse;
use App\Entity\Event\SluggableEditedEvent;
use App\Entity\User;
use App\Repository\PostCategoryRepository;
use App\Security\Voter\PostCategoryVoter;
use App\Service\EntityService\PostCategoryService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateController extends AbstractController
{
	use JsonNormalizedMessages, JsonNormalizedResponse;
	
	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	
	/**
	 * @var EventDispatcherInterface
	 */
	private EventDispatcherInterface $eventDispatcher;
	
	/**
	 * @var ValidatorInterface
	 */
	private ValidatorInterface $validator;
	
	/**
	 * @var PostCategoryRepository
	 */
	private PostCategoryRepository $categoryRepository;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		EventDispatcherInterface $eventDispatcher,
		ValidatorInterface $validator,
		PostCategoryRepository $categoryRepository
	)
	{
		$this->entityManager      = $entityManager;
		$this->eventDispatcher    = $eventDispatcher;
		$this->validator          = $validator;
		$this->categoryRepository = $categoryRepository;
	}
	
	public function __invoke(string $id, Request $request): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$category = $this->getCategoryRepository()->find($id);
		
		if (! $category) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf(
					'Category with id "%s" not found',
					$id
				)
			);
		}
		
		$this->denyAccessUnlessGranted(PostCategoryVoter::EDIT, $category);
		
		$category = PostCategoryService::update($request->getContent(), $category);
		
		$violations = $this->getValidator()->validate($category);
		
		if (count($violations) > 1) {
			
			return $this->jsonViolations($violations);
		}
		
		$this->getEventDispatcher()->dispatch(new SluggableEditedEvent($category));
		
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($category, ['category:read']);
	}
	
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	public function getEventDispatcher(): EventDispatcherInterface
	{
		return $this->eventDispatcher;
	}
	
	public function getValidator(): ValidatorInterface
	{
		return $this->validator;
	}
	
	public function getCategoryRepository(): PostCategoryRepository
	{
		return $this->categoryRepository;
	}
}