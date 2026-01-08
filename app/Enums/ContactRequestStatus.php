<?php

namespace App\Enums;

enum ContactRequestStatus: string
{
    case Pending  = 'pending';
    case Read     = 'read';
    case Replied  = 'replied';
    case Rejected = 'rejected';
    case Spam     = 'spam';
    case Archived = 'archived';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function canTransitionTo(self $new): bool
    {
        return match ($this) {
            self::Pending  => in_array($new, [self::Read, self::Spam]),
            self::Read     => in_array($new, [self::Replied, self::Rejected]),
            self::Replied  => [self::Archived],
            self::Rejected => [self::Archived],
            default        => false,
        };
    }
}
