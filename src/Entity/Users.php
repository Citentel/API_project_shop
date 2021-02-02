<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UsersRepository::class)
 */
class Users implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $lastname;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $password;

    /**
     * @ORM\ManyToOne(targetEntity=Roles::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private Roles $role;

    /**
     * @ORM\OneToOne(targetEntity=UsersHelper::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private UsersHelper $helper;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        return [$this->getRole()];
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function getUsername(): string
    {
        return $this->getEmail();
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getRole(): Roles
    {
        return $this->role;
    }

    public function setRole(Roles $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getHelper(): UsersHelper
    {
        return $this->helper;
    }

    public function setHelper(UsersHelper $helper): self
    {
        $this->helper = $helper;

        return $this;
    }
}
