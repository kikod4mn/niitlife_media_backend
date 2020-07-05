<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Controller\Concerns\ManagesEntities;
use App\Controller\Concerns\ReturnsJsonErrors;
use App\Controller\Concerns\ReturnsNormalizedJson;
use App\Controller\Concerns\UsesXmlMapping;
use App\Entity\Contracts\Trashable;
use App\Entity\Event\AuthorableCreatedEvent;
use App\Entity\Event\SluggableCreatedEvent;
use App\Entity\Event\TimeStampableCreatedEvent;
use App\Entity\Event\TimeStampableUpdatedEvent;
use App\Entity\Event\UuidableCreatedEvent;
use App\Entity\Factory\PostFactory;
use App\Entity\User;
use App\Repository\ImageRepository;
use App\Repository\PostCategoryRepository;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use App\Security\Voter\PostVoter;
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
 * @Route("/posts")
 */
final class PostController extends AbstractController
{
	use UsesXmlMapping, ReturnsJsonErrors, ReturnsNormalizedJson, ManagesEntities;
	
	/**
	 * @var PostRepository
	 */
	private PostRepository $postRepository;
	
	/**
	 * PostController constructor.
	 * @param  string                  $projectDir
	 * @param  EntityManagerInterface  $entityManager
	 * @param  PostRepository          $postRepository
	 */
	public function __construct(string $projectDir, EntityManagerInterface $entityManager, PostRepository $postRepository)
	{
		$this->createSerializer($projectDir);
		$this->postRepository = $postRepository;
		$this->entityManager  = $entityManager;
	}
	
	/**
	 * @Route("/{page}", name="post.list", methods={"GET"}, defaults={"page": 1 }, requirements={"page"="\d+"})
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
		           ->select('p')
		           ->from('App\Entity\Post', 'p')
		           ->where('p.publishedAt IS NOT NULL')
		           ->andWhere('p.trashedAt IS NULL')
		           ->getQuery()
		;
		
		$limit = $request->get('limit', 10);
		
		$pagination = $paginator->paginate($qb, $page, $limit);
		
		$currentPage = $pagination->getCurrentPageNumber();
		$lastPage    = $pagination->getTotalItemCount() / $pagination->getItemNumberPerPage();
		
		$posts = [];
		
		foreach ($pagination->getItems() as $post) {
			$posts[] = $this->getSerializer()->normalize($post, 'json', ['groups' => ['post:list']]);
		}
		
		return $this->json(['posts' => $posts, 'currentPage' => $currentPage, 'lastPage' => $lastPage]);
	}
	
	/**
	 * @Route("/{id}", name="post.by.id", methods={"GET"}, requirements={"id"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  string  $id
	 * @return JsonResponse
	 */
	public function postById(string $id): JsonResponse
	{
		$post = $this->getPostRepository()->find($id);
		
		if (! $post) {
			
			return $this->jsonError(Response::HTTP_NOT_FOUND, sprintf('Post with id "%s" not found', $id));
		}
		
		$this->denyAccessUnlessGranted(PostVoter::VIEW, $post);
		
		return $this->jsonNormalized($post, ['post:read']);
	}
	
	/**
	 * @Route("/{slug}", name="post.by.slug", methods={"GET"})
	 * @param  string  $slug
	 * @return JsonResponse
	 */
	public function postBySlug(string $slug): JsonResponse
	{
		$post = $this->getPostRepository()->findOneBy(['slug' => $slug]);
		
		if (! $post) {
			
			return $this->jsonError(Response::HTTP_NOT_FOUND, sprintf('Post with slug "%s" not found', $slug));
		}
		
		$this->denyAccessUnlessGranted(PostVoter::VIEW, $post);
		
		return $this->jsonNormalized($post, ['post:read']);
	}
	
	/**
	 * @Route("", name="post.create", methods={"POST"})
	 * @param  Request                   $request
	 * @param  EventDispatcherInterface  $eventDispatcher
	 * @return JsonResponse
	 */
	public function create(
		Request $request,
		EventDispatcherInterface $eventDispatcher
	): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		try {
			$post = PostFactory::make($request->getContent());
		} catch (Throwable $e) {
			
			return $this->jsonError(Response::HTTP_BAD_REQUEST, $e->getMessage());
		}
		
		$this->denyAccessUnlessGranted(PostVoter::CREATE, $post);
		
		$eventDispatcher->dispatch(new SluggableCreatedEvent($post));
		$eventDispatcher->dispatch(new AuthorableCreatedEvent($post));
		$eventDispatcher->dispatch(new TimeStampableCreatedEvent($post));
		$eventDispatcher->dispatch(new UuidableCreatedEvent($post));
		
		$this->getEntityManager()->persist($post);
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($post, ['post:read']);
	}
	
	/**
	 * @Route("/{id}/update", name="post.update", methods={"PUT"}, requirements={"id"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
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
		
		$post = $this->getPostRepository()->findOneBy(['id' => $id]);
		
		if (! $post) {
			
			return $this->jsonError(Response::HTTP_NOT_FOUND, sprintf('Post with id "%s" not found', $id));
		}
		
		$this->denyAccessUnlessGranted(PostVoter::EDIT, $post);
		
		$post = PostFactory::update($request->getContent(), $post);
		
		$eventDispatcher->dispatch(new TimeStampableUpdatedEvent($post));
		
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($post, ['post:read']);
	}
	
	/**
	 * @Route("/{postId}/image/{imageId}/edit", name="post.edit.image", methods={"PUT"}, requirements={"postId"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}", "imageId"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  string           $postId
	 * @param  string           $imageId
	 * @param  ImageRepository  $imageRepository
	 * @return JsonResponse
	 */
	public function editImage(string $postId, string $imageId, ImageRepository $imageRepository): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$image = $imageRepository->find($imageId);
		$post  = $this->getPostRepository()->find($postId);
		
		if (! $post) {
			
			return $this->jsonError(Response::HTTP_NOT_FOUND, sprintf('Post with id "%s" not found', $postId));
		}
		
		if (! $image) {
			
			return $this->jsonError(Response::HTTP_NOT_FOUND, sprintf('Image with id "%s" not found', $imageId));
		}
		
		$this->denyAccessUnlessGranted(PostVoter::EDIT, $post);
		
		// todo add header image for the post
		
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($post, ['post:read']);
	}
	
	/**
	 * @Route("/{postId}/category/{categoryId}/edit", name="post.edit.category", methods={"PUT"}, requirements={"postId"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}", "categoryId"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  string                  $postId
	 * @param  string                  $categoryId
	 * @param  PostCategoryRepository  $categoryRepository
	 * @return JsonResponse
	 */
	public function editCategory(string $postId, string $categoryId, PostCategoryRepository $categoryRepository): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$post     = $this->getPostRepository()->find($postId);
		$category = $categoryRepository->find($categoryId);
		
		if (! $post) {
			
			return $this->jsonError(Response::HTTP_NOT_FOUND, sprintf('Post with id "%s" not found', $postId));
		}
		
		if (! $category) {
			
			return $this->jsonError(Response::HTTP_NOT_FOUND, sprintf('Category with id "%s" not found', $categoryId));
		}
		
		$this->denyAccessUnlessGranted(PostVoter::EDIT, $post);
		
		$post->setCategory($category);
		
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($post, ['post:read']);
	}
	
	/**
	 * @Route("/{postId}/tags/edit", name="post.edit.tags", methods={"PUT"} , requirements={"postId"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  string         $postId
	 * @param  Request        $request
	 * @param  TagRepository  $tagRepository
	 * @return JsonResponse
	 */
	public function editTags(string $postId, Request $request, TagRepository $tagRepository): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$post = $this->getPostRepository()->find($postId);
		
		if (! $post) {
			
			return $this->jsonError(Response::HTTP_NOT_FOUND, sprintf('Post with id "%s" not found', $postId));
		}
		
		$tagIds = (json_decode($request->getContent()))->tags;
		
		$tags = $tagRepository->findBy(['id' => $tagIds]);
		
		if (count($tags) > 1) {
			
			return $this->jsonError(Response::HTTP_BAD_REQUEST, sprintf('No tags found for id\'s "%s"', implode(', ', $tagIds)));
		}
		
		$this->denyAccessUnlessGranted(PostVoter::EDIT, $post);
		
		$post->setTags($tags);
		
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($post, ['post:read']);
	}
	
	/**
	 * @Route("/{id}/trash", name="post.delete", methods={"DELETE"}, requirements={"id"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  string  $id
	 * @return JsonResponse
	 */
	public function trash(string $id): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$post = $this->getPostRepository()->find($id);
		
		if (! $post) {
			
			return $this->jsonError(Response::HTTP_NOT_FOUND, sprintf('Post with id "%s" not found', $id));
		}
		
		$this->denyAccessUnlessGranted(PostVoter::DELETE, $post);
		
		$post->trash();
		
		$this->getEntityManager()->flush();
		
		return $this->json(null, Response::HTTP_NO_CONTENT);
	}
	
	/**
	 * @Route("/{id}/destroy", name="post.destroy", methods={"DELETE"}, requirements={"id"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  string  $id
	 * @return JsonResponse
	 */
	public function destroy(string $id): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_ADMINISTRATOR);
		
		$post = $this->getPostRepository()->find($id);
		
		if (! $post) {
			
			return $this->jsonError(Response::HTTP_NOT_FOUND, sprintf('Post with id "%s" not found', $id));
		}
		
		$this->denyAccessUnlessGranted(PostVoter::DELETE, $post);
		
		if ($post instanceof Trashable) {
			
			if (! $post->isTrashed()) {
				
				return $this->jsonError(
					Response::HTTP_FORBIDDEN, sprintf('Post is not yet trashed. Either send the post to trash or use the forceable delete option.')
				);
			}
		}
		
		$this->getEntityManager()->remove($post);
		$this->getEntityManager()->flush();
		
		return $this->json(null, Response::HTTP_NO_CONTENT);
	}
	
	/**
	 * @return PostRepository
	 */
	public function getPostRepository(): PostRepository
	{
		return $this->postRepository;
	}
}