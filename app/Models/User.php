<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'account_name',
        'email',
        'password',
        'web',
        'birthday',
        'introduction',
        'profile_background',
        'avator',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['follow_count', 'follower_count'];

    public function tweets()
    {
        return $this->hasMany(Tweet::class);
    }

    public function follows()
    {
        return $this->belongsToMany(User::class, 'follow_users', 'follower_id', 'user_id')
            ->using(FollowUser::class)
            ->withTimestamps();
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follow_users', 'user_id', 'follower_id')
            ->using(FollowUser::class)
            ->withTimestamps();
    }

    public function likes()
    {
        return $this->belongsToMany(Tweet::class, 'likes')->using(Like::class);
    }

    public function reTweets()
    {
        return $this->belongsToMany(Tweet::class, 'retweets')->using(Like::class);
    }

    public function getFollowCountAttribute()
    {
        return $this->follows()->count();
    }

    public function getFollowerCountAttribute()
    {
        return $this->followers()->count();
    }

    public function getAvatorAttribute($data)
    {
        if (strpos($data, 'https://') !== false) return $data;
        return $data ? Storage::url("users/avatar/{$data}") : null;
    }
}
