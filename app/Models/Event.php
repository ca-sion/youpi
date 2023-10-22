<?php

namespace App\Models;

use App\Enums\AthleteCategory;
use App\Enums\AthleteCategoryGroup;
use App\Enums\EventStatus;
use App\Enums\EventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;

class Event extends Model
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
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'deadline_at' => 'datetime',
        'status' => EventStatus::class,
        'types' => AsEnumCollection::class.':'.EventType::class,
        'athlete_categories' => AsEnumCollection::class.':'.AthleteCategory::class,
        'athlete_category_groups' => AsEnumCollection::class.':'.AthleteCategoryGroup::class,
    ];

    /**
     * Get the event's unicodes.
     */
    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn () => route('events.show', ['event' => $this->id]),
        );
    }

    /**
     * Get the event's unicodes.
     */
    protected function codes(): Attribute
    {
        $value = '';
        foreach ($this->types as $type) {
            $value .= $type->code();
        }
        return Attribute::make(
            get: fn () => $value,
        );
    }

    /**
     * Get the event's athlete categories.
     */
    protected function getAthleteCategories(): Attribute
    {
        $array = [];
        foreach ($this->athlete_categories as $cat) {
            $array[] = $cat->getLabel();
        }
        return Attribute::make(
            get: fn () => implode('-', $array),
        );
    }
}
