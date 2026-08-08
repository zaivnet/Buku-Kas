<?php

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN  = 'admin';
    case STAFF  = 'staff';
    case VIEWER = 'viewer';

    public function label(): string
    {
        return match($this) {
            self::ADMIN  => 'Admin',
            self::STAFF  => 'Staff',
            self::VIEWER => 'Viewer',
        };
    }
}
