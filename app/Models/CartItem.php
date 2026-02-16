<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'guest_cart_token',
        'product_id',
        'quantity'
    ];

    protected $casts = [
        'quantity' => 'integer'
    ];

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }


    public function scopeForGuestsOnly($query)
    {
        return $query->whereNotNull('guest_cart_token');
    }

    public function scopeForGuestToken($query, ?string $guestToken)
    {
        return $query->where('guest_cart_token', $guestToken);
    }
}
