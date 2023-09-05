<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user', 'posts:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user', 'posts:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user', 'posts:read'])]
    private ?string $image = null;

    #[ORM\Column(type: 'date')]
    #[Groups(['user', 'posts:read'])]
    private ?\DateTimeInterface $bday = null;


    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user', 'posts:read'])]
    #[Assert\Email(
        message: 'The email {{ value }} is not a valid email.',
    )]
    private ?string $email = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['user', 'posts:read'])]
    private $isVerified = false;


    #[ORM\Column]
    #[Groups(['user', 'posts:read'])]
    private array $roles = [];

    #[ORM\Column(name: "reset_code", nullable: true)]
    private ?string $resetCode = null;

    /**
     * @return string|null
     */
    public function getResetCode(): ?string
    {
        return $this->resetCode;
    }

    /**
     * @param string|null $resetCode
     */
    public function setResetCode(?string $resetCode): void
    {
        $this->resetCode = $resetCode;
    }
    /**
     */
    private ?string $confirmPassword = null;

    /**
     * @return string|null
     */
    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    /**
     * @param string|null $confirmPassword
     */
    public function setConfirmPassword(?string $confirmPassword): void
    {
        $this->confirmPassword = $confirmPassword;
    }

    /**
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    /**
     * @param bool $isVerified
     */
    public function setIsVerified(bool $isVerified): void
    {
        $this->isVerified = $isVerified;
    }


    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param string|null $image
     */
    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getBday(): ?\DateTimeInterface
    {
        return $this->bday;
    }

    /**
     * @param \DateTimeInterface|null $bday
     */
    public function setBday(?\DateTimeInterface $bday): void
    {
        $this->bday = $bday;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }


}
