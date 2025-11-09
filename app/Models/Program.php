<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        "faculty_id",
        "name",
        "slug",
        "degree_type",
        "duration_semesters",
        "modality",
        "description",
    ];

    /**
     * Get the faculty that owns the program.
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * The people that are associated with the program.
     */
    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, "person_program");
    }
}
