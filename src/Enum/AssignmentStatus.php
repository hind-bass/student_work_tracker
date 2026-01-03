<?php
// src/Enum/AssignmentStatus.php

namespace App\Enum;

enum AssignmentStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled'; // <--- AJOUTEZ CETTE LIGNE

    public function getLabel(): string
    {
        return match($this) {
            self::TODO => 'À faire',
            self::IN_PROGRESS => 'En cours',
            self::COMPLETED => 'Terminé',
            self::CANCELLED => 'Annulé', // <--- AJOUTEZ CETTE LIGNE
        };
    }

    public function getBadgeClass(): string
    {
        return match($this) {
            self::TODO => 'bg-secondary',
            self::IN_PROGRESS => 'bg-primary',
            self::COMPLETED => 'bg-success',
            self::CANCELLED => 'bg-danger', // <--- AJOUTEZ CETTE LIGNE
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::TODO => '📋',
            self::IN_PROGRESS => '⏳',
            self::COMPLETED => '✅',
            self::CANCELLED => '❌', // <--- AJOUTEZ CETTE LIGNE
        };
    }
}
