<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Date;

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
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['user', 'posts:read'])]
    private ?int $etat = 1;

    #[ORM\Column]
    #[Groups(['user', 'posts:read'])]
    private array $roles = [];

    /**
     * @return int|null
     */
    public function getEtat(): ?int
    {
        return $this->etat;
    }

    /**
     * @param int|null $etat
     */
    public function setEtat(?int $etat): void
    {
        $this->etat = $etat;
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
