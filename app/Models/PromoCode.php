<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PromoCode extends Model
{
    use HasFactory;

    protected $table = 'promo_codes';
    protected $primaryKey = 'PromoCodeID';
    protected $guarded = [];

    protected $casts = [
        'email_sent_at' => 'datetime',
    ];

    /**
     * Check if the promo code is valid for use.
     *
     * @param float $subtotal
     * @param int|null $kitchenOwnerId
     * @param int|null $catererId
     * @return bool
     */
    public function isValid($subtotal, $kitchenOwnerId = null, $catererId = null): bool
    {
        // 1. Check if active
        if (!$this->IsActive) {
            return false;
        }

        // 2. Check if expired
        if ($this->ExpiryDate && Carbon::parse($this->ExpiryDate)->isPast()) {
            return false;
        }

        // 3. Check if max uses exceeded
        if ($this->MaxUses !== null && $this->UsedCount >= $this->MaxUses) {
            return false;
        }

        // 4. Check if min order amount is met
        if ($subtotal < $this->MinOrderAmount) {
            return false;
        }
        
        // 5. Vendor validation
        // If promo is owned by a kitchen owner, it can only be applied to that kitchen's items
        if ($this->KitchenOwnerID && $kitchenOwnerId !== null && $this->KitchenOwnerID != $kitchenOwnerId) {
            return false;
        }
        
        // If promo is owned by a caterer, it can only be applied to that caterer's items
        if ($this->CatererID && $catererId !== null && $this->CatererID != $catererId) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount based on subtotal.
     *
     * @param float $subtotal
     * @return float
     */
    public function getDiscountAmount($subtotal): float
    {
        $discount = 0.00;

        if ($this->Type === 'Percentage') {
            $discount = $subtotal * ($this->Value / 100);
        } elseif ($this->Type === 'Fixed') {
            $discount = $this->Value;
        }

        // Discount cannot exceed the subtotal
        return (float) min($subtotal, $discount);
    }
}
