<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    'phone',
    'package_id',
    'referral_code',
    'referred_by',
    'is_admin',
    'is_superadmin',
    'photo',
    ];

    /**
     * Return public URL for user's photo or null.
     */
    public function photoUrl(): ?string
    {
        if (! $this->photo) return null;
        // If photo already contains http(s) assume it's an external URL
        if (preg_match('#^https?://#i', $this->photo)) return $this->photo;
        return asset('storage/' . ltrim($this->photo, '/'));
    }

    public function package()
    {
        return $this->belongsTo(\App\Models\Package::class);
    }

    public function referredBy()
    {
        return $this->belongsTo(self::class, 'referred_by');
    }

    public function referrals()
    {
        return $this->hasMany(self::class, 'referred_by');
    }

    public function coachingTickets()
    {
        return $this->hasMany(\App\Models\CoachingTicket::class);
    }

    /**
     * Check if user has intermediate package access
     * Uses configurable package ID and slugs instead of hardcoded values
     */
    public function hasIntermediateAccess()
    {
        if (!$this->package_id) {
            return false;
        }

        // Check by numeric ID (configurable via settings)
        $intermediatePackageId = \App\Models\Setting::getIntermediatePackageId();
        if ($this->package_id == $intermediatePackageId) {
            return true;
        }

        // Check by package slug (configurable via settings)
        try {
            $package = \App\Models\Package::find($this->package_id);
            if ($package && $package->slug) {
                $allowedSlugs = \App\Models\Setting::getIntermediatePackageSlugs();
                if (in_array($package->slug, $allowedSlugs)) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            // Ignore package lookup failures
        }

        // Check historical purchases via user_packages
        try {
            $allowedSlugs = \App\Models\Setting::getIntermediatePackageSlugs();
            $exists = \App\Models\UserPackage::where('user_id', $this->id)
                ->whereHas('package', function($q) use ($allowedSlugs) {
                    $q->whereIn('slug', $allowedSlugs);
                })
                ->exists();
            
            return $exists;
        } catch (\Throwable $e) {
            // Ignore if UserPackage model doesn't exist or other errors
            return false;
        }
    }

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
            'is_admin' => 'boolean',
            'is_superadmin' => 'boolean',
        ];
    }
}
