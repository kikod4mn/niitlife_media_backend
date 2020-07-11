<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Controller\Concerns\ManagesEntities;
use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\JsonNormalizedResponse;
use App\Controller\Concerns\UsesXmlMapping;
use App\Entity\Contracts\Trashable;
use App\Entity\Event\AuthorableCreatedEvent;
use App\Entity\Event\TimeStampableCreatedEvent;
use App\Entity\Event\TimeStampableUpdatedEvent;
use App\Entity\Event\UuidableCreatedEvent;
use App\Entity\Factory\PostCommentFactory;
use App\Entity\User;
use App\Repository\PostCommentRepository;
use App\Repository\PostRepository;
use App\Security\Voter\PostCommentVoter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

class PostCommentController extends AbstractController
{
	use UsesXmlMapping, JsonNormalizedMessages, JsonNormalizedResponse, ManagesEntities;
	
	/**
	 * @var PostCommentRepository
	 */
	private PostCommentRepository $commentRepository;
	
	/**
	 * PostCommentController constructor.
	 * @param  string                  $projectDir
	 * @param  PostCommentRepository   $commentRepository
	 * @param  EntityManagerInterface  $entityManager
	 */
	public function __construct(
		string $projectDir,
		PostCommentRepository $commentRepository,
		EntityManagerInterface $entityManager
	)
	{
		$this->createSerializer($projectDir);
		$this->commentRepository = $commentRepository;
		$this->entityManager     = $entityManager;
	}
	
	/**
	 * @Route("/posts/{postId}/comments/{page}", name="comments.for.post", methods={"GET"}, defaults={"page": 1 }, requirements={"page"="\d+", "postId"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  string              $postId
	 * @param  int                 $page
	 * @param  Request             $request
	 * @param  PaginatorInterface  $paginator
	 * @param  PostRepository      $postRepository
	 * @return JsonResponse
	 */
	public function list(
		string $postId,
		int $page,
		Request $request,
		PaginatorInterface $paginator,
		PostRepository $postRepository
	): JsonResponse
	{
		$post = $postRepository->find($postId);
		
		if (! $post) {
			
			$this->jsonMessage(Response::HTTP_NOT_FOUND, sprintf('Post with id "%s" not found', $postId));
		}
		
		$qb = $this->getQueryBuilder()
		           ->select('pc')
		           ->from('App\Entity\PostComment', 'pc')
		           ->where('pc.publishedAt IS NOT NULL')
		           ->andWhere('pc.trashedAt IS NULL')
		           ->andWhere('pc.post = :post')
		           ->setParameter('post', $post)
		           ->getQuery()
		;
		
		$limit = $request->get('limit', 10);
		
		$pagination = $paginator->paginate($qb, $page, $limit);
		
		$currentPage = $pagination->getCurrentPageNumber();
		$lastPage    = $pagination->getTotalItemCount() / $pagination->getItemNumberPerPage();
		
		$comments = [];
		
		foreach ($pagination->getItems() as $comment) {
			$comments[] = $this->getSerializer()->normalize($comment, 'json', ['groups' => ['comment:list']]);
		}
		
		return $this->json(['comments' => $comments, 'currentPage' => $currentPage, 'lastPage' => $lastPage]);
	}
	
	/**
	 * @Route("/comments/{id}", name="get.single.comment", methods={"GET"}, requirements={"postId"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  string  $id
	 * @return JsonResponse
	 */
	public function one(string $id): JsonResponse
	{
		$comment = $this->commentRepository->find($id);
		
		if (! $comment) {
			
			return $this->jsonMessage(Response::HTTP_NOT_FOUND, sprintf('Comment with id "%s" not found', $id));
		}
		
		$this->denyAccessUnlessGranted(PostCommentVoter::VIEW, $comment);
		
		return $this->jsonNormalized($comment, ['comment:read']);
	}
	
	/**
	 * @Route("/posts/{postId}/comments", name="post.add.comment", methods={"POST"}, requirements={"postId"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  string                    $postId
	 * @param  Request                   $request
	 * @param  EventDispatcherInterface  $eventDispatcher
	 * @param  PostRepository            $postRepository
	 * @return JsonResponse
	 */
	public function create(
		string $postId,
		Request $request,
		EventDispatcherInterface $eventDispatcher,
		PostRepository $postRepository
	): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_COMMENTATOR);
		
		$post = $postRepository->find($postId);
		
		if (! $post) {
			
			return $this->jsonMessage(Response::HTTP_NOT_FOUND, sprintf('Post with id "%s" not found. Cannot post comment for a nonexistent post.', $postId));
		}
		
		try {
			$comment = PostCommentFactory::make($request->getContent());
		} catch (Throwable $e) {
			
			return $this->jsonMessage(Response::HTTP_BAD_REQUEST, $e->getMessage());
		}
		
		$this->denyAccessUnlessGranted(PostCommentVoter::CREATE, $comment);
		
		$comment->setPost($post);
		
		$eventDispatcher->dispatch(new AuthorableCreatedEvent($comment));
		$eventDispatcher->dispatch(new TimeStampableCreatedEvent($comment));
		$eventDispatcher->dispatch(new UuidableCreatedEvent($comment));
		
		$this->getEntityManager()->persist($comment);
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($post, ['post:read']);
	}
	
	/**
	 * @Route("/comments/{id}/update", name="comment.update", methods={"PUT"}, requirements={"postId"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}", "commentId"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
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
		$this->denyAccessUnlessGranted(User::ROLE_COMMENTATOR);
		
		$comment = $this->commentRepository->find($id);
		
		if (! $comment) {
			
			return $this->jsonMessage(Response::HTTP_NOT_FOUND, sprintf('Comment with id "%s" not found.', $commentId));
		}
		
		$this->denyAccessUnlessGranted(PostCommentVoter::EDIT, $comment);
		
		$comment = PostCommentFactory::update($request->getContent(), $comment);
		
		$eventDispatcher->dispatch(new TimeStampableUpdatedEvent($comment));
		
		$this->getEntityManager()->flush();
		
		return $this->jsonNormalized($comment->getPost(), ['post:read']);
	}
	
	/**
	 * @Route("/comments/{id}/trash", name="comment.trash", methods={"DELETE"}, requirements={"id"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  string  $id
	 * @return JsonResponse
	 */
	public function trash(string $id): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_COMMENTATOR);
		
		$comment = $this->getCommentRepository()->find($id);
		
		if (! $comment) {
			
			return $this->jsonMessage(Response::HTTP_NOT_FOUND, sprintf('Comment with id "%s" not found.', $id));
		}
		
		$this->denyAccessUnlessGranted(PostCommentVoter::DELETE, $comment);
		
		$comment->trash();
		
		$this->getEntityManager()->flush();
		
		return $this->json(null, Response::HTTP_NO_CONTENT);
	}
	
	/**
	 * @Route("/comments/{id}/destroy", name="comment.destroy", methods={"DELETE"}, requirements={"id"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  string  $id
	 * @return JsonResponse
	 */
	public function destroy(string $id): JsonResponse
	{
		$this->denyAccessUnlessGranted(User::ROLE_COMMENTATOR);
		
		$comment = $this->getCommentRepository()->find($id);
		
		if (! $comment) {
			
			return $this->jsonMessage(Response::HTTP_NOT_FOUND, sprintf('Comment with id "%s" not found.', $id));
		}
		
		$this->denyAccessUnlessGranted(PostCommentVoter::DELETE, $comment);
		
		if ($comment instanceof Trashable) {
			
			if (! $comment->isTrashed()) {
				
				return $this->jsonMessage(
					Response::HTTP_FORBIDDEN, 'Comment is not yet trashed. Either send the comment to trash or use the forceable delete option.'
				);
			}
		}
		
		$this->getEntityManager()->remove($comment);
		$this->getEntityManager()->flush();
		
		return $this->json(null, Response::HTTP_NO_CONTENT);
	}
	
	/**
	 * @return PostCommentRepository
	 */
	public function getCommentRepository(): PostCommentRepository
	{
		return $this->commentRepository;
	}
}