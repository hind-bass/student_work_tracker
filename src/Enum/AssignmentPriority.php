<?php
// src/Enum/AssignmentPriority.php

namespace App\Enum;

/**
 * Enum pour les niveaux de priorité des travaux
 * Utilisé dans l'entité Assignment
 */
enum AssignmentPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    /**
     * Retourne le label en français pour l'affichage
     */
    public function getLabel(): string
    {
        return match($this) {
            self::LOW => 'Faible',
            self::MEDIUM => 'Moyenne',
            self::HIGH => 'Haute',
            self::URGENT => 'Urgente',
        };
    }

    /**
     * Retourne la couleur hexadécimale associée
     */
    public function getColor(): string
    {
        return match($this) {
            self::LOW => '#28a745',     // Vert
            self::MEDIUM => '#ffc107',  // Jaune
            self::HIGH => '#fd7e14',    // Orange
            self::URGENT => '#dc3545',  // Rouge
        };
    }

    /**
     * Retourne la classe Bootstrap pour les badges
     */
    public function getBadgeClass(): string
    {
        return match($this) {
            self::LOW => 'bg-success',
            self::MEDIUM => 'bg-warning',
            self::HIGH => 'bg-orange',
            self::URGENT => 'bg-danger',
        };
    }
}
