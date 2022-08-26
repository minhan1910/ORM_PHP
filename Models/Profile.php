<?php

namespace Models;

use Core\Database\ORM\Model;
use Models\User;

class Profile extends Model
{
    protected $table = 'profiles';

    /**
     * One to One
     * user -> hasOne -> profile
     * profile -> belongsTo -> user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
}