<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\AbstractEntity\AbstractEntity;
use App\Entity\Concerns\FilterProfanitiesTrait;
use App\Entity\Concerns\TimeStampableTrait;
use App\Entity\Contracts\TimeStampable;
use App\Repository\UserRepository;
use App\Security\Concerns\ValidatesPassword;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields="email", message="This e-mail is already in use")
 * @UniqueEntity(fields="username", message="This username is already in use")
 */
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
	 * @ORM\Column(type="string", length=255, unique=true)
	 * @Assert\NotBlank()
	 * @Assert\Length(min="6", max="255", minMessage="Minimum length of 6 characters for the username.", maxMessage="Maximum of 255 characters for the username.")
	 * @Groups({"get", "post", "get-post-with-comments"})
	 * @var null|string
	 */
	protected ?string $username = null;
	
	/**
	 * @ORM\Column(type="string", nullable=false)
	 * @var null|string
	 */
	protected ?string $password = null;
	
	/**
	 * @Assert\NotBlank()
	 * @Assert\Regex(
	 *     pattern="/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}/",
	 *     message="Minimum length is 8. The password must also contain one uppercase, one lowercase letter and one digit."
	 * )
	 * @Groups({"post", "put"})
	 * @var null|string
	 */
	protected ?string $plainPassword = null;
	
	/**
	 * @Assert\NotBlank()
	 * @Assert\Expression(
	 *     "this.getPlainPassword() === this.getRetypedPlainPassword()",
	 *     message="Passwords do not match"
	 * )
	 * @Groups({"post", "put"})
	 * @var null|string
	 */
	protected ?string $retypedPlainPassword = null;
	
	/**
	 * @ORM\Column(type="string", length=255, unique=true)
	 * @Assert\NotBlank()
	 * @Assert\Email()
	 * @Assert\Length(max="255", maxMessage="Maximum of 255 characters for the email.")
	 * @Groups({"post", "put"})
	 * @var null|string
	 */
	protected ?string $email = null;
	
	/**
	 * @ORM\Column(type="string", length=255)
	 * @Assert\NotBlank()
	 * @Assert\Length(max="255", maxMessage="Maximum of 255 characters for your name.")
	 * @Groups({"get", "post", "put", "get-post-with-comments"})
	 * @var null|string
	 */
	protected ?string $fullname = null;
	
	/**
	 * @ORM\Column(type="string", nullable=false)
	 * @var null|string
	 */
	protected ?string $role = null;
	
	/**
	 * @ORM\Column(type="boolean", nullable=false)
	 * @var bool
	 */
	protected bool $activated = false;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="author", cascade={"all"})
	 * @var Collection
	 */
	protected Collection $posts;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Post", mappedBy="likedBy", cascade={"all"})
	 * @var Collection
	 */
	protected Collection $postsLiked;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\PostComment", mappedBy="author", cascade={"all"})
	 * @var Collection
	 */
	protected Collection $comments;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\PostComment", mappedBy="likedBy", cascade={"all"})
	 * @var Collection
	 */
	protected Collection $commentsLiked;
	
	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Image", mappedBy="author", cascade={"all"})
	 * @var Collection
	 */
	protected Collection $images;
	
	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Image", mappedBy="likedBy", cascade={"all"})
	 * @var Collection
	 */
	protected Collection $imagesLiked;
	
	/**
	 * @ORM\OneToOne(targetEntity="App\Entity\UserProfile", mappedBy="user", cascade={"all"})
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
		$this->posts         = new ArrayCollection();
		$this->postsLiked    = new ArrayCollection();
		$this->comments      = new ArrayCollection();
		$this->commentsLiked = new ArrayCollection();
		$this->images        = new ArrayCollection();
		$this->imagesLiked   = new ArrayCollection();
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
	public function getComments(): ?Collection
	{
		return $this->comments;
	}
	
	/**
	 * @return null|Collection
	 */
	public function getCommentsLiked(): ?Collection
	{
		return $this->commentsLiked;
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
