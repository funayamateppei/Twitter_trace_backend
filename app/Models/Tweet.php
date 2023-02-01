<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'comment_to',
        'body',
    ];

    protected $appends = [
        'like_count',
        'retweet_count',
        'is_liked',
        'is_retweeted',
    ];

    public function scopeAllTweet($query)
    {
        return $query->whereNull('comment_to')->latest()->with('user', 'images');
    }

    public function scopeComments($query, $id)
    {
        return $query->where('comment_to', $id)->latest()->with('user', 'images');
    }

    public static function scopeAllTweetWithComment($query)
    {
        $tweets = $query->allTweet()->get();

        return $tweets->each(function ($tweet) {
            return $tweet->getCommentStatus($tweet);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(TweetImage::class);
    }

    public function likeUsers()
    {
        return $this->belongsToMany(User::class, 'likes')->using(Like::class);
    }

    public function reTweetUsers()
    {
        return $this->belongsToMany(User::class, 'retweets')->using(Retweet::class);
    }

    public function getLikeCountAttribute()
    {
        return $this->likeUsers()->count();
    }

    public function getRetweetCountAttribute()
    {
        return $this->reTweetUsers()->count();
    }

    public function getIsLikedAttribute()
    {
        $user_id = auth()->id();
        return $this->likeUsers()->exists('user_id', $user_id);
    }

    public function getIsRetweetedAttribute()
    {
        $user_id = auth()->id();
        return $this->reTweetUsers()->exists('user_id', $user_id);
    }

    public function getCommentStatus($tweet)
    {
        $tweet->comments = $this->_getComments($tweet->id);
        $tweet->comment_count = $this->_getCommentCount($tweet->id);
        $tweet->is_commented = $this->_getIsCommented($tweet->id);

        return $tweet;
    }

    private function _getComments($id)
    {
        $comment = $this->comments($id)->get();

        return $comment->each(function ($tweet) {
            return $this->getCommentStatus($tweet);
        });
    }

    private function _getCommentCount($id)
    {
        return $this->comments($id)->count();
    }

    private function _getIsCommented($id)
    {
        $user_id = auth()->id();
        return $this->comments($id)->exists('user_id', $user_id);
    }
}
