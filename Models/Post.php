<?php

namespace Models;

use Core\Database\ORM\Model;
use Models\User;

class Post extends Model
{
    protected $table = 'posts';

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'id');
    }
}

/**
 * 
 * 
 * 
    One-One in Phone hold fk is phone_id or id
    Phone    : $this->belongsTo('App\Author', 'phone_id', 'id');

    Author   : $this->hasOne('App\Phone', 'phone_id', 'id');

    Author   : $this->hasMany('App\Post', 'pst_atr_id', 'atr_id');

    Post     : $this->belongsTo('App\Author', 'pst_atr_id', 'atr_id');

    Post     : $this->belongsToMany('App\Category', 'post_categories', 'pst_id', 'ctg_id');

    Category : $this->belongsToMany('App\Post', 'post_categories', 'ctg_id' 'pst_id');
 */

/**
  
    The best way to define the One - One relationship is to have the secondary table
    lookup a value from the first table.

    Eg: Phone hold the foreign key is user_id of User table. 
    -> Phone chắc chắn có User khi thêm vào.
    -> User có thể có hoặc không có Phone.

    Phone: belongsTo(User::class, 'fk_user_id', 'user_id');
    User : hasOne(Phone::class, 'fk_user_id', 'user_id);

    // Đúng bên User table để kiếm ra cái phone
    $user = User::find(1);
    $phone = $user->phone->id;

    User : hasOne(Phone::class, 'fk_user_id', 'user_id);
    User:
        user: referenceModel referenceTable
        phone: relationModel relationTable
        foreignKey: fk_user_id
        localKey: user_id

        => user_id = localKey = 1

    -> Get phone of user -> $this->where(
                                            $this->relationTable . '.' . $this->foreignKey, 
                                            '=',
                                            $this->{$localKey}
                                        )
                            Result = select phones.* from phones where posts.fk_user_id = 1;

    // Đứng bên phone để kiếm ra cái user mà owner cái phone này.
    
    $phone = Phone::find(1);
    $user = $phone->user->id;
    
    Phone: belongsTo(User::class, 'fk_user_id', 'user_id');
    Phone:
            phone: referenceModel referenceTable
            user: relationModel relationTable
            foreignKey: fk_user_id
            localKey: user_id
        => id (phone's id) = 1  has pk_user_id = 1

    => Get user's phone -> 
    select phones.* from phone where phones.fk_user_id = phones.fk_user_id (1)

    in Relation class has 
        referenceModel: phones instance 
        foreignKey = 'fk_user_id'
        localKey   = 'user_id'
        relationTable: users 
        referenceTable: phones

    Đã có phones.fk_user_id = 1 -> 
    
        $this->where(
            $this->referenceTable . '.' . $this->foreignKey,
            '=',
            $referenceModel->{$foreignKey} // $phone->fk_user_id = 1
        )
   
    Result = select phones.* from phone where phones.fk_user_id = 1


    User -> Profile

    Profile: belongsTo(User::class, 'id', 'user_id');
    Profile: belongsTo(User::class, 'id', 'id');

    $profile = Profile::find(1);
    $user = $profile->user->id;

    in initiatteConnection() 

    belongsTo -> 

     in Relation class has 
        referenceModel: profiles instance 
        foreignKey = 'id'
        localKey   = 'id'
        relationTable: users 
        referenceTable: profiles

    Incorrect: $this->where(
            $this->referenceTable . '.' . $this->foreignKey,
            '=',
            $referenceModel->{$foreignKey} // $profile->id = 1
        )

    Correct: $this->where(
                $this->relationTable . '.' . $this->localKey,
                '=',
                $referenceModel->{$foreignKey}
            );
    
    profiles.id = $profile->id;
    profiles.id = 1

    Incorrect: select profiles.* from profiles where profiles.id = 1

    Correct: select users.* form users where users.user_id = profiles.id 

    User: hasOne(Profile::class, 'id', 'user_id')

    $user = User::find(1);
    $phoneId = $user->phone->id;


    trong Model phải có hasOne method 
    hasOne has paramaters: 1. relationClass
                           2. foreignKey
                           3. localKey

    $relation_model = new $relationClass;
    $primaryKey = $this->primaryKey;
    $relation = construct of HasOneRelation class is
            1. $this->table (referenceTable)
            2. $relation_model->getTable() (relationTable)
            3. $foreignKey
            4. $localKey
    $relation->model($relation_model); // reference to Builder class
    if(!$this->{$primaryKey})
        $relation->referenceModel($this);
    $relation->initiateConncetion();

    return $relation;

    ---------------------------
    Trong initiateConnection();

    $referenceModel = $this->referenceModel;
    $localKey = $this->localKey;
    if(!this->connectionInitiated && !empty($referenceModel))
            
    select profiles.* from profiles where profiles.id = 1 
        1 is $referenceModel->{$localKey}

        $this->where(
            $this->relationTable . '.' . $this->foreignKey,
            '=',
            $referenceModel->{$localKey}
        )

        -> profiles.id = $user->user_id (1)

    Phone -> User:
    Correct: select users.* form users where users.user_id = profiles.id 
            select users.* from users where users.id = 1
    User -> Phone:
    select profiles.* from profiles where profiles.id = 1


    User posts
    User: (Post::class, 'user_id', 'id')

    $user = User::find(1);
    $posts = $user->posts->get();

    trong model User phải có hasMany

    hasMany has paramaters are 
            1. $relationClass
            2. $foreignKey
            3. $localKey

    $relation_model = new $relationClass;
    $primaryKey = $this->primaryKey;
    $relation = new HasManyRelation(
        $this->table, (referenceTable)
        $relation_model->getTable(), (relationTable)
        $foreignKey,
        $localKey
    );

    $relation->model($relation_model);
    if(!$this->{$primaryKey})
        $relation->referenceModel($this);
    $relation->initiateConnection();
    
    return $relation; 

    In initiateConnection():

    $referenceModel = $this->referenceModel;
    $localKey = $this->localKey;
    select posts.* from posts where posts.user_id = $user->id (1)

    $this->where(
        $this->relationTable . '.' . $this->foreignKey,
        '=',
        $referenceModel->{$localKey}
    )
    
    

 */