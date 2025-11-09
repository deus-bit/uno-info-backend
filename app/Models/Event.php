<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        "title",
        "slug",
        "summary",
        "content_html",
        "location",
        "starts_at",
        "ends_at",
        "status",
        "banner_media_id",
        "created_by",
        "updated_by",
        "soft_deleted",
    ];

    protected $casts = [
        "starts_at" => "datetime",
        "ends_at" => "datetime",
        "soft_deleted" => "boolean",
    ];

    /**
     * Get the banner media for the event.
     */
    public function bannerMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, "banner_media_id");
    }

    /**
     * Get the user who created the event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, "created_by");
    }

    /**
     * Get the user who last updated the event.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, "updated_by");
    }

    /**
     * The tags that belong to the event.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, "event_tag");
    }
}
