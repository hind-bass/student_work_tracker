<?php
// src/Entity/Assignment.php

namespace App\Entity;

use App\Enum\AssignmentPriority;
use App\Enum\AssignmentStatus;
use App\Repository\AssignmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entité Assignment - Représente un travail/devoir universitaire
 *
 * AMÉLIORATIONS VERSION 2:
 * - Ajout completion_percentage (pourcentage de complétion)
 * - Ajout estimated_hours et actual_hours (gestion du temps)
 * - Ajout completed_at (date de complétion)
 * - Méthodes utilitaires enrichies
 */
#[ORM\Entity(repositoryClass: AssignmentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Assignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le titre doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 5000, maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères')]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: 'La date d\'échéance est obligatoire')]
    #[Assert\GreaterThan('today', message: 'La date d\'échéance doit être dans le futur')]
    private ?\DateTimeInterface $dueDate = null;

    #[ORM\Column(type: 'string', enumType: AssignmentPriority::class)]
    #[Assert\NotNull(message: 'La priorité est obligatoire')]
    private ?AssignmentPriority $priority = null;

    #[ORM\Column(type: 'string', enumType: AssignmentStatus::class)]
    private AssignmentStatus $status = AssignmentStatus::TODO;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    /**
     * NOUVEAU : Pourcentage de complétion (0-100)
     */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le pourcentage doit être entre {{ min }} et {{ max }}')]
    private int $completionPercentage = 0;

    /**
     * NOUVEAU : Heures estimées pour le travail
     */
    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    #[Assert\Positive(message: 'Les heures estimées doivent être positives')]
    private ?float $estimatedHours = null;

    /**
     * NOUVEAU : Heures réellement passées
     */
    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    #[Assert\Positive(message: 'Les heures réelles doivent être positives')]
    private ?float $actualHours = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * NOUVEAU : Date de complétion du travail
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $completedAt = null;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'La matière est obligatoire')]
    private ?Course $course = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = AssignmentStatus::TODO;
        $this->completionPercentage = 0;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();

        // Automatiquement marquer comme complété si 100%
        if ($this->completionPercentage === 100 && $this->status !== AssignmentStatus::COMPLETED) {
            $this->status = AssignmentStatus::COMPLETED;
            $this->completedAt = new \DateTime();
        }

        // Automatiquement mettre completedAt si statut = completed
        if ($this->status === AssignmentStatus::COMPLETED && $this->completedAt === null) {
            $this->completedAt = new \DateTime();
            $this->completionPercentage = 100;
        }
    }

    // ==================== GETTERS & SETTERS ====================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = trim($title);
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description ? trim($description) : null;
        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeInterface $dueDate): static
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function getPriority(): ?AssignmentPriority
    {
        return $this->priority;
    }

    public function setPriority(AssignmentPriority $priority): static
    {
        $this->priority = $priority;
        return $this;
    }

    public function getStatus(): AssignmentStatus
    {
        return $this->status;
    }

    public function setStatus(AssignmentStatus $status): static
    {
        // Si on marque comme complété
        if ($status === AssignmentStatus::COMPLETED && $this->status !== AssignmentStatus::COMPLETED) {
            $this->completedAt = new \DateTime();
            $this->completionPercentage = 100;
        }

        // Si on rouvre le travail
        if ($status !== AssignmentStatus::COMPLETED && $this->status === AssignmentStatus::COMPLETED) {
            $this->completedAt = null;
        }

        $this->status = $status;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getCompletionPercentage(): int
    {
        return $this->completionPercentage;
    }

    public function setCompletionPercentage(int $completionPercentage): static
    {
        $this->completionPercentage = max(0, min(100, $completionPercentage));

        // Auto-complétion si 100%
        if ($this->completionPercentage === 100 && $this->status !== AssignmentStatus::COMPLETED) {
            $this->status = AssignmentStatus::COMPLETED;
            $this->completedAt = new \DateTime();
        }

        return $this;
    }

    public function getEstimatedHours(): ?float
    {
        return $this->estimatedHours;
    }

    public function setEstimatedHours(?float $estimatedHours): static
    {
        $this->estimatedHours = $estimatedHours;
        return $this;
    }

    public function getActualHours(): ?float
    {
        return $this->actualHours;
    }

    public function setActualHours(?float $actualHours): static
    {
        $this->actualHours = $actualHours;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeInterface $completedAt): static
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;
        return $this;
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

    // ==================== MÉTHODES UTILITAIRES ====================

    /**
     * Vérifie si le travail est en retard
     */
    public function isOverdue(): bool
    {
        if ($this->status === AssignmentStatus::COMPLETED || $this->status === AssignmentStatus::CANCELLED) {
            return false;
        }
        return $this->dueDate < new \DateTime();
    }

    /**
     * Retourne le nombre de jours restants (négatif si en retard)
     */
    public function getDaysRemaining(): int
    {
        $now = new \DateTime();
        $interval = $now->diff($this->dueDate);
        return (int) $interval->format('%r%a');
    }

    /**
     * Retourne le nombre d'heures restantes (négatif si en retard)
     */
    public function getHoursRemaining(): int
    {
        $now = new \DateTime();
        $diff = $this->dueDate->getTimestamp() - $now->getTimestamp();
        return (int) ($diff / 3600);
    }

    /**
     * Retourne la classe CSS pour l'urgence
     */
    public function getUrgencyClass(): string
    {
        if ($this->isOverdue()) {
            return 'text-danger fw-bold';
        }

        $days = $this->getDaysRemaining();
        if ($days <= 1) {
            return 'text-danger';
        } elseif ($days <= 3) {
            return 'text-warning';
        } elseif ($days <= 7) {
            return 'text-info';
        }
        return 'text-success';
    }

    /**
     * Retourne l'icône selon l'urgence
     */
    public function getUrgencyIcon(): string
    {
        if ($this->isOverdue()) {
            return 'bi-exclamation-triangle-fill';
        }

        $days = $this->getDaysRemaining();
        if ($days <= 1) {
            return 'bi-alarm-fill';
        } elseif ($days <= 3) {
            return 'bi-clock-history';
        }
        return 'bi-calendar-check';
    }

    /**
     * NOUVEAU : Retourne le temps restant estimé basé sur la complétion
     */
    public function getEstimatedTimeRemaining(): ?float
    {
        if ($this->estimatedHours === null) {
            return null;
        }

        $remainingPercentage = 100 - $this->completionPercentage;
        return ($this->estimatedHours * $remainingPercentage) / 100;
    }

    /**
     * NOUVEAU : Retourne la différence entre estimé et réel
     */
    public function getTimeDifference(): ?float
    {
        if ($this->estimatedHours === null || $this->actualHours === null) {
            return null;
        }
        return $this->actualHours - $this->estimatedHours;
    }

    /**
     * NOUVEAU : Retourne si on est dans les temps
     */
    public function isOnSchedule(): ?bool
    {
        if ($this->estimatedHours === null || $this->actualHours === null) {
            return null;
        }
        return $this->actualHours <= $this->estimatedHours;
    }

    /**
     * NOUVEAU : Retourne la classe CSS de la barre de progression
     */
    public function getProgressBarClass(): string
    {
        if ($this->completionPercentage === 0) {
            return 'bg-secondary';
        } elseif ($this->completionPercentage < 30) {
            return 'bg-danger';
        } elseif ($this->completionPercentage < 70) {
            return 'bg-warning';
        } elseif ($this->completionPercentage < 100) {
            return 'bg-info';
        }
        return 'bg-success';
    }

    /**
     * Retourne un résumé formaté du travail
     */
    public function getSummary(): string
    {
        $days = $this->getDaysRemaining();
        $daysText = $days > 0 ? "dans $days jour(s)" : "en retard de " . abs($days) . " jour(s)";

        return sprintf(
            '%s - %s - %s (%s%%)',
            $this->title,
            $this->status->getLabel(),
            $daysText,
            $this->completionPercentage
        );
    }

    public function __toString(): string
    {
        return $this->title ?? 'Nouveau travail';
    }
}
