<?php

namespace App\Entity;

use App\Repository\TypeDocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeDocumentRepository::class)]
class TypeDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $obligatoire = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'typeDocument')]
    private Collection $documents;

    #[ORM\ManyToMany(targetEntity: NatureContrat::class, mappedBy: 'typeDocuments')]
    private Collection $natureContrats;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->natureContrats = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setTypeDocument($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): static
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getTypeDocument() === $this) {
                $document->setTypeDocument(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, NatureContrat>
     */
    public function getNatureContrats(): Collection
    {
        return $this->natureContrats;
    }

    public function addNatureContrat(NatureContrat $natureContrat): static
    {
        if (!$this->natureContrats->contains($natureContrat)) {
            $this->natureContrats->add($natureContrat);
            $natureContrat->addTypeDocument($this);
        }

        return $this;
    }

    public function removeNatureContrat(NatureContrat $natureContrat): static
    {
        if ($this->natureContrats->removeElement($natureContrat)) {
            $natureContrat->removeTypeDocument($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getNom() ?? '';
    }
}
