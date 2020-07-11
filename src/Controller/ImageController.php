<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Controller\Concerns\ManagesEntities;
use App\Controller\Concerns\JsonNormalizedResponse;
use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\UsesXmlMapping;
use App\Entity\Contracts\Trashable;
use App\Entity\Event\AuthorableCreatedEvent;
use App\Entity\Event\SluggableCreatedEvent;
use App\Entity\Event\TimeStampableCreatedEvent;
use App\Entity\Event\TimeStampableUpdatedEvent;
use App\Entity\Event\UuidableCreatedEvent;
use App\Entity\Factory\ImageFactory;
use App\Entity\User;
use App\Repository\ImageCategoryRepository;
use App\Repository\ImageRepository;
use App\Repository\TagRepository;
use App\Security\Voter\ImageVoter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

/**
 * @Route("/images")
 */
class ImageController extends AbstractController
{
	use UsesXmlMapping, JsonNormalizedResponse, JsonNormalizedMessages, ManagesEntities;
	
	/**
	 * @var ImageRepository
	 */
	private ImageRepository $imageRepository;
	
	/**
	 * ImageController constructor.
	 * @param  string                  $projectDir
	 * @param  EntityManagerInterface  $entityManager
	 * @param  ImageRepository         $imageRepository
	 */
	public function __construct(string $projectDir, EntityManagerInterface $entityManager, ImageRepository $imageRepository)
	{
		$this->createSerializer($projectDir);
		$this->entityManager   = $entityManager;
		$this->imageRepository = $imageRepository;
	}
	
	/**
	 * @Route("/{page}", name="image.list", methods={"GET"}, defaults={"page": 1 }, requirements={"page"="\d+"})
	 * @param  Request             $request
	 * @param  PaginatorInterface  $paginator
	 * @param  int                 $page
	 * @return JsonResponse
	 */
	public function list(
		Request $request,
		PaginatorInterface $paginator,
		int $page = 1
	): JsonResponse
	{
		$qb = $this->getQueryBuilder()
		           ->select('i')
		           ->from('App\Entity\Image', 'i')
		           ->where('i.publishedAt IS NOT NULL')
		           ->andWhere('i.trashedAt IS NULL')
		           ->getQuery()
		;
		
		$limit = $request->get('limit', 10);
		
		$pagination = $paginator->paginate($qb, $page, $limit);
		
		$currentPage = $pagination->getCurrentPageNumber();
		$lastPage    = $pagination->getTotalItemCount() / $pagination->getItemNumberPerPage();
		
		$images = [];
		
		foreach ($pagination->getItems() as $image) {
			
			if ($this->isGranted(ImageVoter::VIEW, $image)) {
				
				$images[] = $this->getSerializer()->normalize(
					$image, 'json', ['groups' => ['image:list']]
				)
				;
			}
		}
		
		return $this->json(
			[
				'images'      => $images,
				'currentPage' => $currentPage,
				'lastPage'    => $lastPage,
			]
		);
	}
	
	/**
	 * @Route("/{id}", name="image.by.id", methods={"GET"}, requirements={"id"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  string  $id
	 * @return JsonResponse
	 */
	public function imageById(string $id): JsonResponse
	{
		$image = $this->getImageRepository()->find($id);
		
		if (! $image) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf('Image with id "%s" not found', $id)
			);
		}
		
		$this->denyAccessUnlessGranted(ImageVoter::VIEW, $image);
		
		return $this->jsonNormalized($image, ['image:read']);
	}
	
	/**
	 * @Route("/{slug}", name="image.by.slug", methods={"GET"})
	 * @param  string  $slug
	 * @return JsonResponse
	 */
	public function imageBySlug(string $slug): JsonResponse
	{
		$image = $this->getImageRepository()->findOneBy(['slug' => $slug]);
		
		if (! $image) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf('Image with slug "%s" not found', $slug)
			);
		}
		
		$this->denyAccessUnlessGranted(ImageVoter::VIEW, $image);
		
		return $this->jsonNormalized($image, ['image:read']);
	}
	
	/**
	 * @Route("", name="image.create", methods={"POST"})
	 * @param  Request                   $request
	 * @param  EventDispatcherInterface  $eventDispatcher
	 * @return JsonResponse
	 */
	public function create(Request $request, EventDispatcherInterface $eventDispatcher): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		try {
			
			$image = ImageFactory::make($request->getContent());
		} catch (Throwable $e) {
			
			return $this->jsonMessage(
				Response::HTTP_BAD_REQUEST,
				$e->getMessage()
			);
		}
		
		$this->denyAccessUnlessGranted(ImageVoter::CREATE, $image);
		
		$eventDispatcher->dispatch(new SluggableCreatedEvent($image));
		$eventDispatcher->dispatch(new AuthorableCreatedEvent($image));
		$eventDispatcher->dispatch(new TimeStampableCreatedEvent($image));
		$eventDispatcher->dispatch(new UuidableCreatedEvent($image));
		
		$this->getEntityManager()->persist($image);
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($image, ['image:read']);
	}
	
	/**
	 * @Route("/{id}/update", name="image.update", methods={"PUT"}, requirements={"id"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  string                    $id
	 * @param  Request                   $request
	 * @param  EventDispatcherInterface  $eventDispatcher
	 * @return JsonResponse
	 */
	public function update(
		string $id,
		Request $request,
		EventDispatcherInterface $eventDispatcher
	): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$image = $this->getImageRepository()->find($id);
		
		if (! $image) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf('Image with id "%s" not found', $id)
			);
		}
		
		$this->denyAccessUnlessGranted(ImageVoter::EDIT, $image);
		
		try {
			
			$image = ImageFactory::update($request->getContent(), $image);
		} catch (Throwable $e) {
			
			return $this->jsonMessage(
				Response::HTTP_BAD_REQUEST,
				$e->getMessage()
			);
		}
		
		$eventDispatcher->dispatch(new TimeStampableUpdatedEvent($image));
		
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($image, ['image:read']);
	}
	
	/**
	 * @Route("/{imageId}/category/{categoryId}/edit", name="image.edit.category", methods={"PUT"}, requirements={"imageId"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}", "categoryId"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  string                   $imageId
	 * @param  string                   $categoryId
	 * @param  ImageCategoryRepository  $categoryRepository
	 * @return JsonResponse
	 */
	public function editCategory(
		string $imageId,
		string $categoryId,
		ImageCategoryRepository $categoryRepository
	): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$image    = $this->getImageRepository()->find($imageId);
		$category = $categoryRepository->find($categoryId);
		
		if (! $image) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf('Image with id "%s" not found', $imageId)
			);
		}
		
		if (! $category) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf('Category with id "%s" not found', $categoryId)
			);
		}
		
		$this->denyAccessUnlessGranted(ImageVoter::EDIT, $image);
		
		$image->setCategory($category);
		
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($image, ['image:read']);
	}
	
	/**
	 * @Route("/{imageId}/tags/edit", name="image.edit.tags", methods={"PUT"} , requirements={"imageId"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  string         $imageId
	 * @param  Request        $request
	 * @param  TagRepository  $tagRepository
	 * @return JsonResponse
	 */
	public function editTags(
		string $imageId,
		Request $request,
		TagRepository $tagRepository
	): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$image = $this->getImageRepository()->find($imageId);
		
		if (! $image) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf('Image with id "%s" not found', $imageId)
			);
		}
		
		$tagIds = (json_decode($request->getContent()))->tags;
		
		$tags = $tagRepository->findBy(['id' => $tagIds]);
		
		if (count($tags) < 1) {
			
			return $this->jsonMessage(
				Response::HTTP_BAD_REQUEST,
				sprintf(
					'No tags found for id\'s "%s"',
					implode(', ', $tagIds)
				)
			);
		}
		
		$this->denyAccessUnlessGranted(ImageVoter::EDIT, $image);
		
		$image->setTags($tags);
		
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($image, ['image:read']);
	}
	
	/**
	 * @Route("/{id}/trash", name="image.trash", methods={"DELETE"}, requirements={"id"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  string  $id
	 * @return JsonResponse
	 */
	public function trash(string $id): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$image = $this->getImageRepository()->find($id);
		
		if (! $image) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf('Image with id "%s" not found', $id)
			);
		}
		
		$this->denyAccessUnlessGranted(ImageVoter::DELETE, $image);
		
		$image->trash();
		
		$this->getEntityManager()->flush();
		
		return $this->json(null, Response::HTTP_NO_CONTENT);
	}
	
	/**
	 * @Route("/{id}/destroy", name="image.destroy", methods={"DELETE"}, requirements={"id"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  string  $id
	 * @return JsonResponse
	 */
	public function destroy(string $id): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$image = $this->getImageRepository()->find($id);
		
		if (! $image) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf('Image with id "%s" not found', $id)
			);
		}
		
		$this->denyAccessUnlessGranted(ImageVoter::DELETE, $image);
		
		if ($image instanceof Trashable) {
			
			if (! $image->isTrashed()) {
				
				return $this->jsonMessage(
					Response::HTTP_FORBIDDEN,
					'Image is not yet trashed. Either send the post to trash or use the forceable delete option.'
				);
			}
		}
		
		$this->getEntityManager()->remove($image);
		$this->getEntityManager()->flush();
		
		return $this->json(null, Response::HTTP_NO_CONTENT);
	}
	
	/**
	 * @return ImageRepository
	 */
	public function getImageRepository(): ImageRepository
	{
		return $this->imageRepository;
	}
}