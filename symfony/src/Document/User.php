<?php

namespace App\Document;

use App\Document\Trait;
use App\Document\AbstractDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ODM\Document(collection: "users")]
class User extends AbstractDocument implements UserInterface, PasswordAuthenticatedUserInterface
{
    use Trait\UpdateTrait;

    #[ODM\Field(type: "string")]
    #[ODM\UniqueIndex]
    private ?string $email = null;

    #[ODM\Field(type: "collection")]
    private array $roles = [];

    #[ODM\Field(type: "string")]
    private ?string $password = null;

    #[ODM\Field(type: "string")]
    private string $status = 'active';

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    #[\Deprecated] 
     public function eraseCredentials(): void
    {

    }
}
