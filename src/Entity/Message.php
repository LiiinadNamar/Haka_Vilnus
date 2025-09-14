<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    private ?Issue $Issue = null;

    #[ORM\Column]
    private ?bool $IsCustomer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getIssue(): ?Issue
    {
        return $this->Issue;
    }

    public function setIssue(?Issue $Issue): static
    {
        $this->Issue = $Issue;

        return $this;
    }

    public function isCustomer(): ?bool
    {
        return $this->IsCustomer;
    }

    public function setIsCustomer(bool $IsCustomer): static
    {
        $this->IsCustomer = $IsCustomer;

        return $this;
    }
}
