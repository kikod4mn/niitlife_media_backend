<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\AbstractEntity\AbstractEntity;
use App\Entity\Concerns\FilterProfanitiesTrait;
use App\Entity\Concerns\TimeStampableTrait;
use App\Entity\Contracts\TimeStampable;
use App\Security\Concerns\ValidatesPassword;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;

class User extends AbstractEntity implements UserInterface, TimeStampable
{
	use ValidatesPassword, FilterProfanitiesTrait, TimeStampableTrait;
	
	public const ROLE_MUTED               = 'ROLE_MUTED';
	
	public const ROLE_USER                = 'ROLE_USER';
	
	public const ROLE_COMMENTATOR         = 'ROLE_COMMENTATOR';
	
	public const ROLE_MODERATOR           = 'ROLE_MODERATOR';
	
	public const ROLE_ADMINISTRATOR       = 'ROLE_ADMINISTRATOR';
	
	public const ROLE_SUPER_ADMINISTRATOR = 'ROLE_SUPER_ADMINISTRATOR';
	
	/**
	 * @var null|string
	 */
	protected ?string $username = null;
	
	/**
	 * @var null|string
	 */
	protected ?string $password = null;
	
	/**
	 * @var null|string
	 */
	protected ?string $plainPassword = null;
	
	/**
	 * @var null|string
	 */
	protected ?string $retypedPlainPassword = null;
	
	/**
	 * @var null|string
	 */
	protected ?string $email = null;
	
	/**
	 * @var null|string
	 */
	protected ?string $fullname = null;
	
	/**
	 * @var null|string
	 */
	protected ?string $role = null;
	
	/**
	 * @var bool
	 */
	protected bool $activated = false;
	
	/**
	 * @var Collection
	 */
	protected Collection $posts;
	
	/**
	 * @var Collection
	 */
	protected Collection $postsLiked;
	
	/**
	 * @var Collection
	 */
	protected Collection $postComments;
	
	/**
	 * @var Collection
	 */
	protected Collection $postCommentsLiked;
	
	/**
	 * @var Collection
	 */
	protected Collection $images;
	
	/**
	 * @var Collection
	 */
	protected Collection $imagesLiked;
	
	/**
	 * @var Collection
	 */
	protected Collection $imageComments;
	
	/**
	 * @var Collection
	 */
	protected Collection $imageCommentsLiked;
	
	/**
	 * @var null|UserProfile
	 */
	protected ?UserProfile $userProfile = null;
	
	/**
	 * User constructor.
	 */
	public function __construct()
	{
		$this->activate();
		$this->setRole(self::ROLE_COMMENTATOR);
		$this->posts              = new ArrayCollection();
		$this->postsLiked         = new ArrayCollection();
		$this->postComments       = new ArrayCollection();
		$this->postCommentsLiked  = new ArrayCollection();
		$this->images             = new ArrayCollection();
		$this->imagesLiked        = new ArrayCollection();
		$this->imageComments      = new ArrayCollection();
		$this->imageCommentsLiked = new ArrayCollection();
	}
	
	/**
	 * @return array
	 */
	public function jsonSerialize(): array
	{
		return [
			'id' => $this->getId(),
		];
	}
	
	/**
	 * @return null|string
	 */
	public function getUsername(): ?string
	{
		return $this->username;
	}
	
	/**
	 * @param  string  $username
	 * @return User
	 */
	public function setUsername(string $username): self
	{
		$this->username = $this->cleanString($username);
		
		return $this;
	}
	
	/**
	 * @return null|string
	 */
	public function getPassword(): ?string
	{
		return $this->password;
	}
	
	/**
	 * @param  string  $password
	 * @return User
	 */
	public function setPassword(string $password): self
	{
		$this->password = $password;
		
		return $this;
	}
	
	/**
	 * @return null|string
	 */
	public function getPlainPassword(): ?string
	{
		return $this->plainPassword;
	}
	
	/**
	 * @param  null|string  $plainPassword
	 * @return User
	 */
	public function setPlainPassword(?string $plainPassword): self
	{
		$this->plainPassword = $plainPassword;
		
		return $this;
	}
	
	/**
	 * @return null|string
	 */
	public function getRetypedPlainPassword(): ?string
	{
		return $this->retypedPlainPassword;
	}
	
	/**
	 * @param  null|string  $retypedPlainPassword
	 * @return $this|User
	 */
	public function setRetypedPlainPassword(?string $retypedPlainPassword): User
	{
		$this->retypedPlainPassword = $retypedPlainPassword;
		
		return $this;
	}
	
	/**
	 * Use password encoder default salting technique. Return null here.
	 * @return null|string
	 */
	public function getSalt(): ?string
	{
		return null;
	}
	
	/**
	 * @return null|string
	 */
	public function getEmail(): ?string
	{
		return $this->email;
	}
	
	/**
	 * @param  string  $email
	 * @return User
	 */
	public function setEmail(string $email): self
	{
		//		if ($email !== $this->cleanString($email)) {
		//			throw new \InvalidArgumentException('Email is dirty');
		//		}
		
		$this->email = $email;
		
		return $this;
	}
	
	/**
	 * @return null|string
	 */
	public function getFullname(): ?string
	{
		return $this->fullname;
	}
	
	/**
	 * @param  string  $fullname
	 * @return User
	 */
	public function setFullname(string $fullname): self
	{
		$this->fullname = $this->cleanString($fullname);
		
		return $this;
	}
	
	/**
	 * UserInterface function
	 * @return null|array
	 */
	public function getRoles(): ?array
	{
		return [$this->role];
	}
	
	/**
	 * @return null|string
	 */
	public function getRole(): ?string
	{
		return $this->role;
	}
	
	/**
	 * @param  string  $role
	 * @return User
	 */
	public function setRole(string $role): self
	{
		$this->role = $role;
		
		return $this;
	}
	
	/**
	 * @return null|bool
	 */
	public function isActivated(): bool
	{
		return $this->activated;
	}
	
	/**
	 * Activate a user manually.
	 */
	public function activate(): void
	{
		$this->activated = true;
	}
	
	/**
	 * Deactivate a user manually.
	 */
	public function deActivate(): void
	{
		$this->activated = false;
	}
	
	/**
	 * @return null|Collection
	 */
	public function getPosts(): ?Collection
	{
		return $this->posts;
	}
	
	/**
	 * @return null|Collection
	 */
	public function getPostsLiked(): ?Collection
	{
		return $this->postsLiked;
	}
	
	/**
	 * @return null|Collection
	 */
	public function getPostComments(): ?Collection
	{
		return $this->postComments;
	}
	
	/**
	 * @return null|Collection
	 */
	public function getPostCommentsLiked(): ?Collection
	{
		return $this->postCommentsLiked;
	}
	
	/**
	 * @return null|Collection
	 */
	public function getImages(): ?Collection
	{
		return $this->images;
	}
	
	/**
	 * @return null|Collection
	 */
	public function getImagesLiked(): ?Collection
	{
		return $this->imagesLiked;
	}
	
	/**
	 * @return null|Collection
	 */
	public function getImageComments(): ?Collection
	{
		return $this->imageComments;
	}
	
	/**
	 * @return null|Collection
	 */
	public function getImageCommentsLiked(): ?Collection
	{
		return $this->imageCommentsLiked;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function eraseCredentials(): void
	{
		$this->setPlainPassword(null);
		$this->setRetypedPlainPassword(null);
	}
	
	/**
	 * @return null|UserProfile
	 */
	public function getProfile(): ?UserProfile
	{
		return $this->userProfile;
	}
	
	/**
	 * @param  UserProfile  $userProfile
	 * @return $this|User
	 */
	public function setProfile(UserProfile $userProfile): User
	{
		$this->userProfile = $userProfile;
		
		return $this;
	}
}
