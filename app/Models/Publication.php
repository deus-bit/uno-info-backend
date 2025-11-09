<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Publication extends Model
{
    use HasFactory;

    protected $fillable = [
        "title",
        "slug",
        "summary",
        "content_html",
        "category_id",
        "status",
        "published_at",
        "cover_media_id",
        "created_by",
        "updated_by",
        "featured",
        "soft_deleted",
    ];

    protected $casts = [
        "published_at" => "datetime",
        "featured" => "boolean",
        "soft_deleted" => "boolean",
    ];

    /**
     * Get the category that owns the publication.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the cover media for the publication.
     */
    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, "cover_media_id");
    }

    /**
     * Get the user who created the publication.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, "created_by");
    }

    /**
     * Get the user who last updated the publication.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, "updated_by");
    }

    /**
     * The tags that belong to the publication.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, "publication_tag");
    }
}
