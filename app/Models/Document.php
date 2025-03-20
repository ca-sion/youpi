<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
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
        'published_on' => 'datetime',
        'expires_on'   => 'datetime',
        'type'         => DocumentType::class,
        'status'       => DocumentStatus::class,
        'sections'     => 'array',
        'travel_data'  => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['number'];

    /**
     * Get the document's number.
     */
    protected function number(): Attribute
    {
        return Attribute::make(
            get: fn () => str_pad($this->id + 100, 3, '0', STR_PAD_LEFT),
        );
    }

    /**
     * Get the document's identifier.
     */
    protected function identifier(): Attribute
    {
        return Attribute::make(
            get: fn () => str($this->type->getLabel())->take(1).$this->number,
        );
    }

    /**
     * Get the document's full name.
     */
    protected function slugName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->published_on->format('Ymd').'-'.$this->identifier,
        );
    }
}
