<?php

namespace Models;

use Core\Database\ORM\Model;
use Models\User;

class Post extends Model
{
    protected $table = 'posts';

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}