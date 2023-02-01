<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Storage;

class TweetImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'tweet_id',
        'image',
    ];

    public function getImageAttribute($data)
    {
        if (strpos($data, 'https://') !== false) return $data;
        return $data ? Storage::url("tweets/images/{$data}") : null;
    }
}
