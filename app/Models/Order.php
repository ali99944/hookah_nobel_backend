<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_phone',

        'status',
        'is_paid',
        'tracking_code',

        'subtotal',
        'fees_cost',
        'shipping_cost',
        'total',

        'address',
        'city',
        'notes',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
    ];

    // Relationships
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function generateTrackingCode(): string
    {
        $timestamp = now()->format('ymd');
        do {
            $random = strtoupper(Str::random(6));
            $code = "HN-$timestamp-$random";
        } while (self::where('tracking_code', $code)->exists());

        return $code;
    }
}
