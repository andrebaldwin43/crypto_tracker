<?php   

namespace App\Document\Trait;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use App\Document\User;

trait UpdateTrait
{
    #[ODM\Field(type: "date")]
    private ?\DateTimeInterface $updatedAt = null;

    #[ODM\ReferenceOne(targetDocument:User::class)]
    private ?User $updatedBy = null;

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedBy(User $updatedBy): self
    {
        $this->updatedBy = $updatedBy;
        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }
}