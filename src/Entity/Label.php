<?php

namespace App\Entity;

use App\Repository\LabelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LabelRepository::class)]
class Label
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $prompt = null;

    #[ORM\Column(length: 255)]
    private ?string $color = null;

    /**
     * @var Collection<int, Issue>
     */
    #[ORM\ManyToMany(targetEntity: Issue::class, inversedBy: 'labels')]
    private Collection $issue;

    public function __construct()
    {
        $this->issue = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrompt(): ?string
    {
        return $this->prompt;
    }

    public function setPrompt(string $prompt): static
    {
        $this->prompt = $prompt;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return Collection<int, Issue>
     */
    public function getIssue(): Collection
    {
        return $this->issue;
    }

    public function addIssue(Issue $issue): static
    {
        if (!$this->issue->contains($issue)) {
            $this->issue->add($issue);
        }

        return $this;
    }

    public function removeIssue(Issue $issue): static
    {
        $this->issue->removeElement($issue);

        return $this;
    }
}
