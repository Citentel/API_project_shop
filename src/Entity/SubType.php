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
     * @ORM\OneToMany(targetEntity=MainType::class, mappedBy="subTypes")
     */
    private $mainTypes;

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

    /**
     * @return Collection|MainType[]
     */
    public function getMainTypes(): Collection
    {
        return $this->mainTypes;
    }

    public function addMainType(MainType $mainType): self
    {
        if (!$this->mainTypes->contains($mainType)) {
            $this->mainTypes[] = $mainType;
            $mainType->setSubTypes($this);
        }

        return $this;
    }

    public function removeMainType(MainType $mainType): self
    {
        if ($this->mainTypes->removeElement($mainType)) {
            // set the owning side to null (unless already changed)
            if ($mainType->getSubTypes() === $this) {
                $mainType->setSubTypes(null);
            }
        }

        return $this;
    }
}
