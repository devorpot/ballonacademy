<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'payment_amount',
        'payment_currency',
        'payment_description',
        'payment_type',
        'payment_card',
        'payment_card_type',
        'payment_card_brand',
        'payment_bank',
        'payment_date',
        'payment_refund_date',
        'payment_refund_desc',
        'payment_status',
        'payment_stripe_id',
        'payment_refund',
        'used_coupon',
        'coupon_id',
        'coupon_discount',
    ];

    protected $casts = [
        'used_coupon' => 'boolean',
        'payment_refund' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'payment_currency');
    }
}
