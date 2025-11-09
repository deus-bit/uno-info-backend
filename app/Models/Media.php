<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $table = "media";

    protected $fillable = [
        "file_name",
        "mime_type",
        "url",
        "size_bytes",
        "alt_text",
        "uploaded_by",
    ];

    /**
     * Get the user that uploaded the media.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, "uploaded_by");
    }
}
