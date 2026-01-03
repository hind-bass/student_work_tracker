<?php
// src/Entity/Course.php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Entité Course - Représente une matière/cours universitaire
 *
 * AMÉLIORATIONS V2:
 * - Description de la matière
 * - Nombre de crédits
 * - Semestre
 * - Date de mise à jour
 */
#[ORM\Entity(repositoryClass: CourseRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(
    fields: ['code', 'user'],
    message: 'Vous avez déjà une matière avec ce code'
)]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom de la matière est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le code de la matière est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Le code doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le code ne peut pas dépasser {{ limit }} caractères'
    )]
    #[Assert\Regex(
        pattern: '/^[A-Z0-9-]+$/i',
        message: 'Le code ne peut contenir que des lettres, chiffres et tirets'
    )]
    private ?string $code = null;

    #[ORM\Column(length: 7)]
    #[Assert\NotBlank(message: 'La couleur est obligatoire')]
    #[Assert\Regex(
        pattern: '/^#[0-9A-Fa-f]{6}$/',
        message: 'La couleur doit être au format hexadécimal (#RRGGBB)'
    )]
    private ?string $color = '#007bff';

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le nom du professeur ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $professor = null;

    /**
     * NOUVEAU : Description de la matière
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 1000,
        maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $description = null;

    /**
     * NOUVEAU : Nombre de crédits ECTS
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Range(
        min: 1,
        max: 30,
        notInRangeMessage: 'Le nombre de crédits doit être entre {{ min }} et {{ max }}'
    )]
    private ?int $credits = null;

    /**
     * NOUVEAU : Semestre (ex: "Automne 2024", "S1", etc.)
     */
    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $semester = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * NOUVEAU : Date de mise à jour
     */
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'courses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, Assignment>
     */
    #[ORM\OneToMany(targetEntity: Assignment::class, mappedBy: 'course', cascade: ['remove'], orphanRemoval: true)]
    private Collection $assignments;

    public function __construct()
    {
        $this->assignments = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->color = '#007bff'; // Bleu par défaut
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // ==================== GETTERS & SETTERS ====================

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
        $this->name = trim($name);
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = strtoupper(trim($code));
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

    public function getProfessor(): ?string
    {
        return $this->professor;
    }

    public function setProfessor(?string $professor): static
    {
        $this->professor = $professor ? trim($professor) : null;
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

    public function getCredits(): ?int
    {
        return $this->credits;
    }

    public function setCredits(?int $credits): static
    {
        $this->credits = $credits;
        return $this;
    }

    public function getSemester(): ?string
    {
        return $this->semester;
    }

    public function setSemester(?string $semester): static
    {
        $this->semester = $semester;
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    // ==================== MÉTHODES DE COLLECTION ====================

    /**
     * @return Collection<int, Assignment>
     */
    public function getAssignments(): Collection
    {
        return $this->assignments;
    }

    public function addAssignment(Assignment $assignment): static
    {
        if (!$this->assignments->contains($assignment)) {
            $this->assignments->add($assignment);
            $assignment->setCourse($this);
        }
        return $this;
    }

    public function removeAssignment(Assignment $assignment): static
    {
        if ($this->assignments->removeElement($assignment)) {
            if ($assignment->getCourse() === $this) {
                $assignment->setCourse(null);
            }
        }
        return $this;
    }

    // ==================== MÉTHODES UTILITAIRES ====================

    /**
     * Retourne le nombre de travaux associés
     */
    public function getAssignmentsCount(): int
    {
        return $this->assignments->count();
    }

    /**
     * Retourne le nombre de travaux terminés
     */
    public function getCompletedAssignmentsCount(): int
    {
        return $this->assignments->filter(function(Assignment $assignment) {
            return $assignment->getStatus()->value === 'completed';
        })->count();
    }

    /**
     * Retourne le nombre de travaux en cours
     */
    public function getInProgressAssignmentsCount(): int
    {
        return $this->assignments->filter(function(Assignment $assignment) {
            return $assignment->getStatus()->value === 'in_progress';
        })->count();
    }

    /**
     * Retourne le nombre de travaux à faire
     */
    public function getTodoAssignmentsCount(): int
    {
        return $this->assignments->filter(function(Assignment $assignment) {
            return $assignment->getStatus()->value === 'todo';
        })->count();
    }

    /**
     * Retourne le pourcentage de complétion des travaux de ce cours
     */
    public function getCompletionPercentage(): float
    {
        $total = $this->getAssignmentsCount();
        if ($total === 0) {
            return 0.0;
        }
        $completed = $this->getCompletedAssignmentsCount();
        return round(($completed / $total) * 100, 2);
    }

    /**
     * Retourne la classe CSS de la barre de progression
     */
    public function getProgressBarClass(): string
    {
        $percentage = $this->getCompletionPercentage();

        if ($percentage === 0) {
            return 'bg-secondary';
        } elseif ($percentage < 30) {
            return 'bg-danger';
        } elseif ($percentage < 70) {
            return 'bg-warning';
        } elseif ($percentage < 100) {
            return 'bg-info';
        }
        return 'bg-success';
    }

    /**
     * Retourne la couleur avec opacité pour les fonds
     */
    public function getColorWithOpacity(float $opacity = 0.1): string
    {
        // Convertir hex en RGB
        $hex = str_replace('#', '', $this->color);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "rgba($r, $g, $b, $opacity)";
    }

    /**
     * Vérifie si la matière a des travaux en retard
     */
    public function hasOverdueAssignments(): bool
    {
        foreach ($this->assignments as $assignment) {
            if ($assignment->isOverdue()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retourne le nombre de travaux en retard
     */
    public function getOverdueAssignmentsCount(): int
    {
        return $this->assignments->filter(function(Assignment $assignment) {
            return $assignment->isOverdue();
        })->count();
    }

    /**
     * Pour affichage dans les formulaires
     */
    public function __toString(): string
    {
        return $this->name . ' (' . $this->code . ')';
    }

    /**
     * Retourne un résumé formaté de la matière
     */
    public function getSummary(): string
    {
        return sprintf(
            '%s (%s) - %d travaux - %s%% complété',
            $this->name,
            $this->code,
            $this->getAssignmentsCount(),
            $this->getCompletionPercentage()
        );
    }
}
