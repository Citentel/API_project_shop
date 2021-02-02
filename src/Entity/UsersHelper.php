<?php

namespace App\Entity;

use App\Repository\UsersHelperRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UsersHelperRepository::class)
 */
class UsersHelper
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int  $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $restartCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $verifiCode;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $wasDeleted;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRestartCode(): ?string
    {
        return $this->restartCode;
    }

    public function setRestartCode(?string $restartCode): self
    {
        $this->restartCode = $restartCode;

        return $this;
    }

    public function getVerifiCode(): ?string
    {
        return $this->verifiCode;
    }

    public function setVerifiCode(?string $verifiCode): self
    {
        $this->verifiCode = $verifiCode;

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
