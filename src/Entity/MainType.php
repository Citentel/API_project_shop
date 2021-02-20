<?php

namespace App\Entity;

use App\Repository\MainTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MainTypeRepository::class)
 */
class MainType
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
     * @ORM\ManyToMany(targetEntity=Products::class, inversedBy="mainTypes")
     */
    private $products;

    /**
     * @ORM\OneToMany(targetEntity=SubType::class, mappedBy="mainType")
     */
    private $subTypes;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->subTypes = new ArrayCollection();
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
     * @return Collection|SubType[]
     */
    public function getSubTypes(): Collection
    {
        return $this->subTypes;
    }

    public function addSubType(SubType $subType): self
    {
        if (!$this->subTypes->contains($subType)) {
            $this->subTypes[] = $subType;
            $subType->setMainType($this);
        }

        return $this;
    }

    public function removeSubType(SubType $subType): self
    {
        if ($this->subTypes->removeElement($subType)) {
            // set the owning side to null (unless already changed)
            if ($subType->getMainType() === $this) {
                $subType->setMainType(null);
            }
        }

        return $this;
    }
}
