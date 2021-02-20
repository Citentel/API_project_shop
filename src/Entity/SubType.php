<?php

namespace App\Entity;

use App\Repository\SubTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SubTypeRepository::class)
 */
class SubType
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity=Products::class, inversedBy="subTypes")
     */
    private $products;

    /**
     * @ORM\ManyToOne(targetEntity=MainType::class, inversedBy="subTypes")
     */
    private $mainType;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->mainTypes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getMainType(): ?MainType
    {
        return $this->mainType;
    }

    public function setMainType(?MainType $mainType): self
    {
        $this->mainType = $mainType;

        return $this;
    }
}
