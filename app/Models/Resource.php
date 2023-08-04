<?php

namespace App\Models;

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
     * Get the athlete group that owns the resource.
     */
    public function athleteGroup(): BelongsTo
    {
        return $this->belongsTo(AthleteGroup::class);
    }

    /**
     * Get the resource first media url.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function firstMediaUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstMediaUrl('resources'),
        );
    }

    /**
     * Get the resource first media.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function firstMedia(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstMedia('resources'),
        );
    }

    /**
     * Get the resource url.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function attachment(): Attribute
    {
        if (! empty($this->url)) {
            $value = $this->url;
        } elseif (! empty($this->firstMedia) && $this->firstMediaUrl) {
            $value = 'https://drive.google.com/viewer?embedded=true&hl=fr-CH&url=' . $this->firstMediaUrl;
        } else {
            $value = null;
        }

        return Attribute::make(
            get: fn () => $value,
        );
    }
}
