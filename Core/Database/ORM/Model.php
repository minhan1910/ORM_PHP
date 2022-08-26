<?php

namespace Core\Database\ORM;

use Core\Database\DB;
use Core\Database\ORM\Relations\BelongsToRelation;
use Core\Helpers;
use Core\Database\ORM\Relations\HasManyRelation;
use Core\Database\ORM\Relations\HasOneRelation;

class Model
{
    protected $table = '';
    protected $primaryKey = 'id';

    /** 
     * Called directly update and delete method for
     * deleting and updating all records in model
     */
    protected static $builder_callable_methods = [
        'all', // implementing
        'select',
        'join',
        'sum',
        'avg',
        'count',
        'min',
        'max',
        'limit',
        'offset',
        'first',
        'find',
        'create',
        'update',
        'delete',
        'where',
        'orWhere',
        'whereIn',
        'orWhereIn',
    ];

    public static function __callStatic($method, $args)
    {
        $class = get_called_class(); // get name of class when called method
        if (in_array($method, static::$builder_callable_methods)) {
            $builder = new Builder;
            $model_instance = new $class;
            $builder->model($model_instance);
            return call_user_func_array([$builder, $method], $args);
        }
        return call_user_func_array([$class, $method], $args);
    }

    /**
     * Example:
     * $user = User::find(1)
     * $relation= $user
     *              ->posts()
     *              ->toSql();
     * => Build Query:
     *  select posts.* 
     *  from posts
     *  where posts.userId = 1;
     */
    public function hasMany($relationClass, $foreignKey, $localKey = null)
    {
        $localKey = !empty($localKey)  ? $localKey : $this->primaryKey;
        $relation_model = new $relationClass;
        //$this->table is instance like user->post() -> $this is user 
        $relation = new HasManyRelation(
            $this->table,
            $relation_model->getTable(),
            $foreignKey,
            $localKey
        );
        $primaryKey = $this->primaryKey;
        $relation->model($relation_model); // model from Builder
        if (isset($this->{$primaryKey}))  // check must be real model 
            $relation->referenceModel($this);
        $relation->initiateConnection();

        return $relation;
    }

    /**
     * How to use and build query ?
     * 
     * With specific post id
     * $post = Post::find(1);
     * $relation = $post->user()->toSql();
     * Query: select users.* from users where users.id = 1 (1 is posts.userId)
     * NOT: select users.* from users where posts.userId = 1
     * -----------------------------------------------------
     * Not id in $post -> can get all users
     * If $post = new Post; 
     * $relation = $post->user()->toSql();
     * 
     * Query: select users.* from users
     */
    public function belongsTo($relationClass, $foreignKey, $localKey = null)
    {
        $localKey = !empty($localKey) ? $localKey : $this->primaryKey;
        $relation_model = new $relationClass;
        $relation = new BelongsToRelation(
            $this->table,
            $relation_model->getTable(),
            $foreignKey,
            $localKey
        );
        $relation->model($relation_model);
        $primaryKey = $this->primaryKey;
        if (!empty($this->{$primaryKey}))
            $relation->referenceModel($this);
        $relation->initiateConnection();

        return $relation;
    }

    /**
     * user hasOne profile
     * $user = User::find(1);
     * $relation = $user->profile()->toSql();
     * 
     * Query: select profiles.* from profiles where users.profileId = 1
     * 1 is $user->id <=> $this->{$primaryKey}
     */
    public function hasOne($relationClass)
    {
        $relation_model = new $relationClass;
        $primaryKey = $this->primaryKey;
        $relation = new HasOneRelation(
            $this->table,
            $relation_model->getTable(),
            $relation_model->getPrimaryKey(),
            $primaryKey
        );
        $relation->model($relation_model);
        if (!empty($this->{$primaryKey}))
            $relation->referenceModel($this);
        $relation->initiateConnection();

        return $relation;
    }


    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    /** 
     * Use:
     * $instanceModel = Model::find(1);
     * $instanceModel->delete();
     */
    public function delete()
    {
        $pk = $this->primaryKey;

        // Must be have to instance model after retrieving by find or first method;
        if (!isset($this->{$pk}))
            return false;

        // $this->pk when user->id = 3 -> $this->id = 3
        return DB::getInstance()
            ->table($this->table)
            ->where($this->primaryKey, '=', $this->{$pk})
            ->delete();
    }

    public function update(array $data)
    {
        $pk = $this->primaryKey;

        if (!isset($this->{$pk}))
            return false;

        return DB::getInstance()
            ->table($this->table)
            ->where($this->primaryKey, '=', $this->{$pk})
            ->update($data);
    }

    /**
     * How can use this method ?
     * 
     * $instanceModel = new Model;
     * $instanceModel->name = 'test';
     * $instanceModel->age = 12;
     * $instanceModel->save()
     * 
     * @property $data is get all properties of instanceModel like name, age.
     * @property $cols is get all columns of table in database
     * @property $this->primaryKey is 'id' (string)
     * @property $this->$primaryKey ($this->id) is numeric result
     * 
     * If instanceModel has $id -> existed
     *      => Update it.
     * Else => Create new instance and update new $id into it.
     *  
     */
    public function save()
    {
        $data = Helpers::get_object_public_fields($this);
        $cols = DB::getInstance()
            ->fetch('SHOW COLUMNS FROM ' . $this->table);

        // Drop unexpected fields
        foreach ($data as $name => $value) {
            $filtered_cols = array_filter($cols, function ($col) use ($name) {
                return $name === $col->Field;
            });
            if (empty($filtered_cols))
                unset($data[$name]);
        }

        $primaryKey = $this->primaryKey;

        // value of $primaryKey is 'id'
        if (isset($this->{$primaryKey}))
            return DB::getInstance()
                ->table($this->table)
                ->where($this->primaryKey, '=', $this->{$primaryKey})
                ->update($data);

        $id = DB::getInstance()
            ->table($this->table)
            ->insertGetId($data);
        $this->{$primaryKey} = $id; // set id for for new instance

        return $id;
    }
}