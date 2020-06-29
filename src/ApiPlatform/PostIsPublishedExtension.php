<?php

declare(strict_types = 1);

namespace App\ApiPlatform;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

class PostIsPublishedExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
	/**
	 * @var Security
	 */
	private Security $security;
	
	/**
	 * PostIsPublishedExtension constructor.
	 * @param  Security  $security
	 */
	public function __construct(Security $security)
	{
		$this->security = $security;
	}
	
	/**
	 * @param  QueryBuilder                 $queryBuilder
	 * @param  QueryNameGeneratorInterface  $queryNameGenerator
	 * @param  string                       $resourceClass
	 * @param  null|string                  $operationName
	 */
	public function applyToCollection(
		QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null
	): void
	{
		$this->addWhere($resourceClass, $queryBuilder);
	}
	
	/**
	 * @param  QueryBuilder                 $queryBuilder
	 * @param  QueryNameGeneratorInterface  $queryNameGenerator
	 * @param  string                       $resourceClass
	 * @param  array                        $identifiers
	 * @param  null|string                  $operationName
	 * @param  array                        $context
	 */
	public function applyToItem(
		QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null,
		array $context = []
	): void
	{
		$this->addWhere($resourceClass, $queryBuilder);
	}
	
	/**
	 * @param  string        $resourceClass
	 * @param  QueryBuilder  $queryBuilder
	 */
	public function addWhere(string $resourceClass, QueryBuilder $queryBuilder): void
	{
		if ($resourceClass !== Post::class) {
			
			return;
		}
		
		if ($this->getSecurity()->isGranted(User::ROLE_ADMINISTRATOR)) {
			return;
		}
		
		$rootAlias = $queryBuilder->getRootAliases()[0];
		$queryBuilder->andWhere(sprintf("%s.publishedAt IS NOT NULL", $rootAlias));
	}
	
	/**
	 * @return Security
	 */
	public function getSecurity(): Security
	{
		return $this->security;
	}
}