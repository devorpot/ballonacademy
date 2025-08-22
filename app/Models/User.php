<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Spatie\Permission\Traits\HasRoles;

use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;
 
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',

    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
              'active' => 'boolean',
        ];
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function courses()
{
    return $this->belongsToMany(Course::class, 'subscriptions', 'user_id', 'course_id')
        ->as('subscription') // se accede como $course->subscription
        ->withPivot([
            'id',
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
            'created_at', 'updated_at', // deja estas solo si existen en la tabla
        ])
        ->withTimestamps(); // deja esto solo si la tabla subscriptions tiene timestamps
}

 public function lessons()
    {
        return $this->hasMany(\App\Models\Lesson::class);
    }

     public function getAssignedCourseIdAttribute()
    {
        return $this->courses()->first()?->id;
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
    
}
