<?php

namespace App\Entity;

use App\Repository\ProductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductsRepository::class)
 */
class Products
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
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $priceCrossed;

    /**
     * @ORM\Column(type="integer")
     */
    private int $price;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $ammount;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $display;

    /**
     * @ORM\OneToMany(targetEntity=ProductsImages::class, mappedBy="products")
     */
    private $images;

    /**
     * @ORM\ManyToMany(targetEntity=MainType::class, mappedBy="products")
     */
    private $mainTypes;

    /**
     * @ORM\ManyToMany(targetEntity=SubType::class, mappedBy="products")
     */
    private $subTypes;

    /**
     * @ORM\ManyToMany(targetEntity=SizeType::class, mappedBy="products")
     */
    private $sizeTypes;

    /**
     * @ORM\ManyToMany(targetEntity=SexType::class, inversedBy="products")
     */
    private $sexTypes;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->mainTypes = new ArrayCollection();
        $this->subTypes = new ArrayCollection();
        $this->sizeTypes = new ArrayCollection();
        $this->sexTypes = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPriceCrossed(): ?int
    {
        return $this->priceCrossed;
    }

    public function setPriceCrossed(int $priceCrossed): self
    {
        $this->priceCrossed = $priceCrossed;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getAmmount(): ?int
    {
        return $this->ammount;
    }

    public function setAmmount(int $ammount): self
    {
        $this->ammount = $ammount;

        return $this;
    }

    public function getDisplay(): ?bool
    {
        return $this->display;
    }

    public function setDisplay(bool $display): self
    {
        $this->display = $display;

        return $this;
    }

    /**
     * @return Collection|ProductsImages[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(ProductsImages $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setProducts($this);
        }

        return $this;
    }

    public function removeImage(ProductsImages $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProducts() === $this) {
                $image->setProducts(null);
            }
        }

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
            $mainType->addProduct($this);
        }

        return $this;
    }

    public function removeMainType(MainType $mainType): self
    {
        if ($this->mainTypes->removeElement($mainType)) {
            $mainType->removeProduct($this);
        }

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
            $subType->addProduct($this);
        }

        return $this;
    }

    public function removeSubType(SubType $subType): self
    {
        if ($this->subTypes->removeElement($subType)) {
            $subType->removeProduct($this);
        }

        return $this;
    }

    /**
     * @return Collection|SizeType[]
     */
    public function getSizeTypes(): Collection
    {
        return $this->sizeTypes;
    }

    public function addSizeType(SizeType $sizeType): self
    {
        if (!$this->sizeTypes->contains($sizeType)) {
            $this->sizeTypes[] = $sizeType;
            $sizeType->addProduct($this);
        }

        return $this;
    }

    public function removeSizeType(SizeType $sizeType): self
    {
        if ($this->sizeTypes->removeElement($sizeType)) {
            $sizeType->removeProduct($this);
        }

        return $this;
    }

    /**
     * @return Collection|SexType[]
     */
    public function getSexTypes(): Collection
    {
        return $this->sexTypes;
    }

    public function addSexType(SexType $sexType): self
    {
        if (!$this->sexTypes->contains($sexType)) {
            $this->sexTypes[] = $sexType;
        }

        return $this;
    }

    public function removeSexType(SexType $sexType): self
    {
        $this->sexTypes->removeElement($sexType);

        return $this;
    }
}
