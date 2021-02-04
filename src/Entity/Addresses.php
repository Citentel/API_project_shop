<?php

namespace App\Entity;

use App\Repository\AddressesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AddressesRepository::class)
 */
class Addresses
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
    private string $city;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $street;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $homeNumber;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $premisesNumber;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $zip;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $display;

    /**
     * @ORM\ManyToOne(targetEntity=Users::class, inversedBy="addresses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity=Countries::class, inversedBy="addresses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $country;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getHomeNumber(): int
    {
        return $this->homeNumber;
    }

    public function setHomeNumber(int $homeNumber): self
    {
        $this->homeNumber = $homeNumber;

        return $this;
    }

    public function getPremisesNumber(): ?int
    {
        return $this->premisesNumber;
    }

    public function setPremisesNumber(?int $premisesNumber): self
    {
        $this->premisesNumber = $premisesNumber;

        return $this;
    }

    public function getZip(): int
    {
        return $this->zip;
    }

    public function setZip(int $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    public function getDisplay(): ?bool
    {
        return $this->display;
    }

    public function setDisplay(?bool $display): self
    {
        $this->display = $display;

        return $this;
    }

    public function getUsers(): ?Users
    {
        return $this->users;
    }

    public function setUsers(?Users $users): self
    {
        $this->users = $users;

        return $this;
    }

    public function getCountry(): ?Countries
    {
        return $this->country;
    }

    public function setCountry(?Countries $country): self
    {
        $this->country = $country;

        return $this;
    }
}
