<?php

namespace Models;

use Core\Database\ORM\Model;
use Models\{Post, Profile};

class User extends Model
{
    protected $table = 'users';

    public function posts()
    {
        return $this->hasMany(Post::class, 'userId', 'id');
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    // public function ratedPosts()
    // {
    //     return $this->belongsToMany(Post::class, 'ratings', 'userId', 'postId')->withPivot('rating');
    // }

    public function ratedPosts()
    {
        return $this->belongsToMany('Models\Post', 'ratings', 'userId', 'postId')->withPivot('rating');
    }
}