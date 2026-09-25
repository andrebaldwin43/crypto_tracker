<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\MappedSuperclass]
abstract class AbstractDocument
{
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: "date")]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct() 
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    // public function setCreatedAt(\DateTimeInterface $createdAt): self
    // {
    //     $this->createdAt = $createdAt;
    //     return $this;
    // }
}
