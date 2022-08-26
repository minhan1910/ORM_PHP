<?php

namespace Models;

use Core\Database\ORM\Model;
use Models\Post;

class User extends Model
{
    protected $table = 'users';

    public function posts()
    {
        return $this->hasMany(Post::class, 'userId', 'id');
    }
}