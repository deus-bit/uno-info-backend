<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Person extends Model
{
    use HasFactory;

    protected $fillable = [
        "full_name",
        "position_title",
        "email",
        "phone",
        "biography",
        "photo_media_id",
    ];

    /**
     * Get the photo media for the person.
     */
    public function photoMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, "photo_media_id");
    }

    /**
     * The programs that the person is associated with.
     */
    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, "person_program");
    }
}
