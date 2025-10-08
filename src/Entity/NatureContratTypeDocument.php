<?php

namespace App\Entity;

use App\Repository\NatureContratTypeDocumentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NatureContratTypeDocumentRepository::class)]
class NatureContratTypeDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'natureContratTypeDocuments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?NatureContrat $natureContrat = null;

    #[ORM\ManyToOne(inversedBy: 'natureContratTypeDocuments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Document $document = null;

    #[ORM\Column]
    private ?bool $obligatoire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNatureContrat(): ?NatureContrat
    {
        return $this->natureContrat;
    }

    public function setNatureContrat(?NatureContrat $natureContrat): static
    {
        $this->natureContrat = $natureContrat;

        return $this;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(?Document $document): static
    {
        $this->document = $document;

        return $this;
    }

    public function isObligatoire(): ?bool
    {
        return $this->obligatoire;
    }

    public function setObligatoire(bool $obligatoire): static
    {
        $this->obligatoire = $obligatoire;

        return $this;
    }
}
