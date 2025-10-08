<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10, unique: true)]
    private ?string $abreviation = null;

    #[ORM\Column(length: 255)]
    private ?string $libelleComplet = null;

    #[ORM\Column(length: 100)]
    private ?string $typeDocument = null;

    #[ORM\Column(length: 255)]
    private ?string $usage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filename = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $fileType = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $uploadedBy = null;

    #[ORM\Column(type: 'boolean')]
    private bool $obligatoire = false;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?int $fileSize = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    private ?Dossier $dossier = null;

    #[ORM\OneToMany(targetEntity: NatureContratTypeDocument::class, mappedBy: 'document', cascade: ['persist', 'remove'])]
    private Collection $natureContratTypeDocuments;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->natureContratTypeDocuments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAbreviation(): ?string
    {
        return $this->abreviation;
    }

    public function setAbreviation(string $abreviation): static
    {
        $this->abreviation = $abreviation;

        return $this;
    }

    public function getLibelleComplet(): ?string
    {
        return $this->libelleComplet;
    }

    public function setLibelleComplet(string $libelleComplet): static
    {
        $this->libelleComplet = $libelleComplet;

        return $this;
    }

    public function getTypeDocument(): ?string
    {
        return $this->typeDocument;
    }

    public function setTypeDocument(string $typeDocument): static
    {
        $this->typeDocument = $typeDocument;

        return $this;
    }

    public function getUsage(): ?string
    {
        return $this->usage;
    }

    public function setUsage(string $usage): static
    {
        $this->usage = $usage;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    public function setFileType(?string $fileType): static
    {
        $this->fileType = $fileType;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;

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

    public function getUploadedBy(): ?string
    {
        return $this->uploadedBy;
    }

    public function setUploadedBy(?string $uploadedBy): static
    {
        $this->uploadedBy = $uploadedBy;

        return $this;
    }

    public function isObligatoire(): bool
    {
        return $this->obligatoire;
    }

    public function setObligatoire(bool $obligatoire): static
    {
        $this->obligatoire = $obligatoire;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(?int $fileSize): static
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getFileSizeFormatted(): string
    {
        if (!$this->fileSize) {
            return 'N/A';
        }

        $bytes = $this->fileSize;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDossier(): ?Dossier
    {
        return $this->dossier;
    }

    public function setDossier(?Dossier $dossier): static
    {
        $this->dossier = $dossier;

        return $this;
    }

    /**
     * @return Collection<int, NatureContratTypeDocument>
     */
    public function getNatureContratTypeDocuments(): Collection
    {
        return $this->natureContratTypeDocuments;
    }

    public function addNatureContratTypeDocument(NatureContratTypeDocument $natureContratTypeDocument): static
    {
        if (!$this->natureContratTypeDocuments->contains($natureContratTypeDocument)) {
            $this->natureContratTypeDocuments->add($natureContratTypeDocument);
            $natureContratTypeDocument->setDocument($this);
        }

        return $this;
    }

    public function removeNatureContratTypeDocument(NatureContratTypeDocument $natureContratTypeDocument): static
    {
        if ($this->natureContratTypeDocuments->removeElement($natureContratTypeDocument)) {
            // set the owning side to null (unless already changed)
            if ($natureContratTypeDocument->getDocument() === $this) {
                $natureContratTypeDocument->setDocument(null);
            }
        }

        return $this;
    }
}