<?php

namespace App\Document\Trait;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use App\Document\User;

trait CreateTrait
{

    #[ODM\Field(type: "date")]
    private ?\User $createdBy = null;

    public function setCreatedBy(\User $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getCreatedBy(): ?\User
    {
        return $this->createdBy;
    }
}