<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Resource extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

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
        'is_protected'       => 'boolean',
        'available_weekdays' => 'array',
    ];

    /**
     * Get the athlete group that owns the resource.
     */
    public function athleteGroup(): BelongsTo
    {
        return $this->belongsTo(AthleteGroup::class);
    }

    /**
     * Get the resource first media url.
     */
    protected function firstMediaUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstMediaUrl('resources'),
        );
    }

    /**
     * Get the resource first media.
     */
    protected function firstMedia(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstMedia('resources'),
        );
    }

    /**
     * GChecket the resource media is a pdf.
     */
    public function mediaIsPdf(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->firstMedia?->mime_type == 'application/pdf',
        );
    }

    /**
     * Get the resource attachment.
     */
    protected function attachment(): Attribute
    {
        if (! empty($this->url)) {
            $value = $this->url;
        } elseif (! empty($this->firstMedia) && $this->firstMediaUrl) {
            if ($this->mediaIsPdf) {
                $value = $this->firstMediaUrl;
            } else {
                $value = 'https://drive.google.com/viewer?embedded=true&hl=fr-CH&url='.$this->firstMediaUrl;
            }
        } else {
            $value = null;
        }

        return Attribute::make(
            get: fn () => $value,
        );
    }

    /**
     * Get the resource url to share.
     */
    protected function shareUrl(): Attribute
    {
        if ($this->attachment_type == 'url' || $this->url != null) {
            $value = $this->url;
        } else {
            $value = route('resources.view', ['resource' => $this]);
        }

        return Attribute::make(
            get: fn () => $value,
        );
    }

    /**
     * Computed name service.
     */
    private function computedNameService($whithWeek = true, $whithAthleteGroup = true, $whithName = true): string
    {
        $cDate = Carbon::parse($this->date);
        $cDateEnd = Carbon::parse($this->date_end);
        $year = $cDate->year;
        $yearEnd = $cDateEnd->year;
        $week = $cDate->weekOfYear;
        $day = $cDate->day;
        $dayName = $cDate->locale('fr')->dayName;
        $type = $this->type;
        $group = data_get($this, 'athleteGroup.name');
        $name = $this->name;

        $hasYear = in_array($type, ['year_plan', 'macro_plan', 'micro_plan']);
        $hasWeek = $whithWeek && in_array($type, ['week_plan', 'day_plan', 'session']);
        $hasDay = $type == 'session';
        $hasGroup = ! empty($group) && $whithAthleteGroup;

        $value = '';
        if ($hasYear) {
            $value .= ($year == $yearEnd) ? $year : $year.'-'.$yearEnd;
        }
        $value .= ($hasWeek && $hasYear ? ' · ' : null);
        if ($hasWeek) {
            $value .= 'Semaine '.$week;
        }
        $value .= ($hasWeek && $hasDay ? ' · ' : null);
        if ($hasDay) {
            $value .= str($dayName)->ucfirst().' '.$day;
        }
        $value .= (($hasGroup && $hasYear) || ($hasGroup && $hasDay) || ($hasGroup && $hasWeek) ? ' · ' : null);
        if ($hasGroup) {
            $value .= $group;
        }
        $value .= (($hasYear || $hasWeek || $hasDay || $hasGroup) && $name ? ' · ' : null);
        if (! empty($name) && $whithName) {
            $value .= $name;
        }

        return $value;
    }

    /**
     * Get the computed name without week.
     */
    protected function computedName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->computedNameService(),
        );
    }

    /**
     * Get the computed name without week.
     */
    protected function computedNameWithoutWeek(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->computedNameService(false),
        );
    }

    /**
     * Get the computed name without week.
     */
    protected function computedNameWithoutGroup(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->computedNameService(true, false),
        );
    }

    /**
     * Check if the resource is accessible.
     */
    protected function isAccessible(): Attribute
    {
        $value = true;
        $now = now();
        $nowTime = $now->timezone(config('youpi.timezone'))->isoFormat('hhmm');

        $checkTime = Carbon::parse($this->available_time_start)->isoFormat('hhmm') <= $nowTime;
        $checkWeekdays = in_array($now->weekday(), $this->available_weekdays ?? []);

        if ($this->is_protected) {
            $value = false;
            $valueTime = false;
            $valueWeekday = false;

            if ($checkTime) {
                $valueTime = true;
                if (empty($this->available_time_start)) {
                    $valueTime = false;
                }
            }
            if ($checkWeekdays) {
                $valueWeekday = true;
            }
            if (empty($this->available_weekdays)) {
                $valueWeekday = true;
            }
            if ($valueTime && $valueWeekday) {
                $value = true;
            }
        }

        return Attribute::make(
            get: fn () => $value,
        );
    }
}
