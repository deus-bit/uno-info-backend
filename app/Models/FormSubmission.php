<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        "form_id",
        "submitted_at",
        "ip_address",
        "payload_json",
        "attachment_media_id",
    ];

    protected $casts = [
        "submitted_at" => "datetime",
        "payload_json" => "array", // Cast to array for easier handling
    ];

    /**
     * Get the form that this submission belongs to.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get the attachment media for this submission.
     */
    public function attachmentMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, "attachment_media_id");
    }
}
