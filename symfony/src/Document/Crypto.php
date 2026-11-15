<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use App\Document\Trait;
use DateTimeInterface;

#[ODM\Document]
class Crypto extends AbstractDocument
{
    use Trait\UpdateTrait;
    use Trait\UserTrait;

    #[ODM\Field(type: "string")]
    private ?string $name = null;

    #[ODM\Field(type: "float")]
    private ?float $price = null;

    #[ODM\Field(type: "float")]
    private ?float $quantity = null;

    #[ODM\Field(type: "date")]
    private ?DateTimeInterface $purchaseDate = null;

    #[ODM\Field(type: "string")]
    private ?string $cryptoCurrency = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setCoin(string $name, string $cryptoCurrency): self
    {
        $this->name = $name;
        $this->cryptoCurrency = $cryptoCurrency;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(?float $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getPurchaseDate(): ?DateTimeInterface
    {
        return $this->purchaseDate;
    }

    public function setPurchaseDate(?DateTimeInterface $purchaseDate): self
    {
        $this->purchaseDate = $purchaseDate;
        return $this;
    }

    public function getCrypto(): ?string
    {
        return $this->cryptoCurrency;
    }
}
