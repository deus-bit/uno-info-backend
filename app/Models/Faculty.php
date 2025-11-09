<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    use HasFactory;

    protected $fillable = ["name", "slug", "description", "dean_person_id"];

    /**
     * Get the dean of the faculty.
     */
    public function dean(): BelongsTo
    {
        return $this->belongsTo(Person::class, "dean_person_id");
    }

    /**
     * Get the programs for the faculty.
     */
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }
}
