<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\ContactRequestStatus;
use Illuminate\Database\Eloquent\Builder;

class ContactRequest extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
        'read_at',
        'replied_at',
    ];

    protected $casts = [
        'status'     => ContactRequestStatus::class,
        'read_at'    => 'datetime',
        'replied_at' => 'datetime',
    ];

    /* --------------------
       Query Scopes
    -------------------- */

    public function scopePending(Builder $query)
    {
        return $query->where('status', ContactRequestStatus::Pending);
    }

    public function scopeUnread(Builder $query)
    {
        return $query->where('status', '!=', ContactRequestStatus::Read);
    }
}
