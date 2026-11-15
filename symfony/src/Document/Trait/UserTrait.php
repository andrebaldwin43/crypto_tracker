<?php
namespace App\Document\Trait;

use App\Document\User;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

trait UserTrait
{
    #[ODM\ReferenceOne(targetDocument: User::class)]
    #[Assert\NotNull(message: 'A document must be associated with a user.')]
    private ?User $user = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }
}