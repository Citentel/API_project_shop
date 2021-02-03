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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $archivedEmail;

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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $verifyCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $restartCode;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $wasDeleted;

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

    public function getArchivedEmail(): ?string
    {
        return $this->archivedEmail;
    }

    public function setArchivedEmail(?string $archivedEmail): self
    {
        $this->archivedEmail = $archivedEmail;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = (string)password_hash($password, PASSWORD_BCRYPT);

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

    public function getVerifyCode(): ?string
    {
        return $this->verifyCode;
    }

    public function setVerifyCode(?string $verifyCode): self
    {
        if ($verifyCode !== null) {
            $this->verifyCode = (string)password_hash($verifyCode, PASSWORD_BCRYPT);

            return $this;
        }

        $this->verifyCode = null;

        return $this;
    }

    public function getRestartCode(): ?string
    {
        return $this->restartCode;
    }

    public function setRestartCode(?string $restartCode): self
    {
        if ($restartCode !== null) {
            $this->restartCode = (string)password_hash($restartCode, PASSWORD_BCRYPT);

            return $this;
        }

        $this->restartCode = null;

        return $this;
    }

    public function getWasDeleted(): ?bool
    {
        return $this->wasDeleted;
    }

    public function setWasDeleted(?bool $wasDeleted): self
    {
        $this->wasDeleted = $wasDeleted;

        return $this;
    }
}
