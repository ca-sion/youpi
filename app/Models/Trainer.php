<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Trainer extends Model
{
    use HasFactory;

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
    protected $casts = [];

    /**
     * Get the athletes groups for the trainer.
     */
    public function athleteGroups(): BelongsToMany
    {
        return $this->belongsToMany(AthleteGroup::class);
    }

    /**
     * Get the athletes groups categories for the trainer.
     */
    protected function athleteGroupsCategories(): Attribute
    {
        $athleteGroups = $this->athleteGroups;
        $collection = collect();

        foreach ($athleteGroups as $ag) {
            $collection->add($ag->categories);
        }

        $collection = $collection->collapse()->unique();

        return Attribute::make(
            get: fn () => $collection,
        );
    }

    /**
     * Get the athletes groups categories for the trainer.
     */
    protected function athleteGroupsCategoriesGroup(): Attribute
    {
        $athleteGroups = $this->athleteGroups;
        $collection = collect();

        foreach ($athleteGroups as $ag) {
            $collection->add($ag->categories->map(function ($cat) {
                return $cat->group();
            }));
        }

        $collection = $collection->collapse()->unique();

        return Attribute::make(
            get: fn () => $collection,
        );
    }
}
