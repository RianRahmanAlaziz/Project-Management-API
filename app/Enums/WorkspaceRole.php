<?php

namespace App\Enums;

enum WorkspaceRole: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case MEMBER = 'member';
    case VIEWER = 'viewer';

    /**
     * Role yang boleh mengelola workspace.
     *
     * @return list<self>
     */
    public static function managers(): array
    {
        return [
            self::OWNER,
            self::ADMIN,
        ];
    }

    /**
     * Mengambil semua nilai enum untuk validasi.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $role): string => $role->value,
            self::cases(),
        );
    }
}
