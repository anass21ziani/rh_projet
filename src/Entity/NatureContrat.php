<?php

namespace App\Entity;

use App\Repository\NatureContratRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NatureContratRepository::class)]
#[ORM\Table(name: '`nature_contrat`')]
class NatureContrat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(targetEntity: EmployeeContrat::class, mappedBy: 'natureContrat')]
    private Collection $employeeContrats;

    #[ORM\ManyToMany(targetEntity: TypeDocument::class, inversedBy: 'natureContrats')]
    private Collection $typeDocuments;

    public function __construct()
    {
        $this->employeeContrats = new ArrayCollection();
        $this->typeDocuments = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getEmployeeContrats(): Collection
    {
        return $this->employeeContrats;
    }

    public function addEmployeeContrat(EmployeeContrat $employeeContrat): static
    {
        if (!$this->employeeContrats->contains($employeeContrat)) {
            $this->employeeContrats->add($employeeContrat);
            $employeeContrat->setNatureContrat($this);
        }
        return $this;
    }

    public function removeEmployeeContrat(EmployeeContrat $employeeContrat): static
    {
        if ($this->employeeContrats->removeElement($employeeContrat)) {
            if ($employeeContrat->getNatureContrat() === $this) {
                $employeeContrat->setNatureContrat(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, TypeDocument>
     */
    public function getTypeDocuments(): Collection
    {
        return $this->typeDocuments;
    }

    public function addTypeDocument(TypeDocument $typeDocument): static
    {
        if (!$this->typeDocuments->contains($typeDocument)) {
            $this->typeDocuments->add($typeDocument);
        }

        return $this;
    }

    public function removeTypeDocument(TypeDocument $typeDocument): static
    {
        $this->typeDocuments->removeElement($typeDocument);

        return $this;
    }

    public function __toString(): string
    {
        return $this->libelle ?? '';
    }
}
