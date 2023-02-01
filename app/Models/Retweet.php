<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Retweet extends Pivot
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tweet_id',
    ];

    protected $table = 'retweets';
}
