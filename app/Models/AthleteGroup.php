<?php

namespace App\Models;

use App\Enums\AthleteCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AthleteGroup extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'categories' => AsEnumCollection::class.':'.AthleteCategory::class,
    ];

    /**
     * Get the resources for the athlete group.
     */
    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    /**
     * Get the trainer that owns the athlete group.
     */
    public function trainers(): BelongsToMany
    {
        return $this->belongsToMany(Trainer::class);
    }
}
