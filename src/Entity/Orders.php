<?php

namespace App\Entity;

use App\Repository\OrdersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrdersRepository::class)
 */
class Orders
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
    private string $hash;

    /**
     * @ORM\ManyToOne(targetEntity=Users::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $users;

    /**
     * @ORM\ManyToMany(targetEntity=Products::class, inversedBy="orders")
     */
    private $products;

    /**
     * @ORM\ManyToOne(targetEntity=Status::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=Payment::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $payment;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $pricePay;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private \DateTimeInterface $dataCreated;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private \DateTimeInterface $dataUpdated;

    /**
     * @ORM\ManyToOne(targetEntity=Addresses::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $address;

    /**
     * @ORM\ManyToOne(targetEntity=Delivery::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $delivery;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

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

    /**
     * @return Collection|Products[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Products $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
        }

        return $this;
    }

    public function removeProduct(Products $product): self
    {
        $this->products->removeElement($product);

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): self
    {
        $this->payment = $payment;

        return $this;
    }

    public function getPricePay(): ?int
    {
        return $this->pricePay;
    }

    public function setPricePay(int $pricePay): self
    {
        $this->pricePay = $pricePay;

        return $this;
    }

    public function getDataCreated(): ?\DateTimeInterface
    {
        return $this->dataCreated;
    }

    public function setDataCreated(\DateTimeInterface $dataCreated): self
    {
        $this->dataCreated = $dataCreated;

        return $this;
    }

    public function getDataUpdated(): ?\DateTimeInterface
    {
        return $this->dataUpdated;
    }

    public function setDataUpdated(\DateTimeInterface $dataUpdated): self
    {
        $this->dataUpdated = $dataUpdated;

        return $this;
    }

    public function getAddress(): ?Addresses
    {
        return $this->address;
    }

    public function setAddress(?Addresses $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getDelivery(): ?Delivery
    {
        return $this->delivery;
    }

    public function setDelivery(?Delivery $delivery): self
    {
        $this->delivery = $delivery;

        return $this;
    }
}
