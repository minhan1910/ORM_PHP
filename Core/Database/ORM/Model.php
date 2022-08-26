<?php

namespace Core\Database\ORM;

use Core\Database\DB;
use Core\Helpers;

class Model
{
    protected $table = '';
    protected $primaryKey = 'id';

    /** 
     * Called directly update and delete method for
     * deleting and updating all records in model
     */
    protected static $builder_callable_methods = [
        'all', 'where', 'orWhere', 'whereIn',
        'orWhereIn', 'select', 'join', 'sum',
        'avg', 'count', 'min', 'max',
        'limit', 'offset', 'first', 'find',
        'create', 'update', 'delete'
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
        if (!isset($this->$pk))
            return false;

        // $this->pk when user->id = 3 -> $this->id = 3
        return DB::getInstance()
            ->table($this->table)
            ->where($this->primaryKey, '=', $this->$pk)
            ->delete();
    }

    public function update(array $data)
    {
        $pk = $this->primaryKey;

        if (!isset($this->$pk))
            return false;

        return DB::getInstance()
            ->table($this->table)
            ->where($this->primaryKey, '=', $this->$pk)
            ->update($data);
    }


    public function save()
    {
        $data = Helpers::get_object_public_fields($this);
        $cols = DB::getInstance()->fetch('SHOW COLUMNS FROM ' . $this->table);

        // Drop unexpected fields
        foreach ($data as $name => $value) {
            $filtered_cols = array_filter($cols, function ($col) use ($name) {
                return $name === $col->Field;
            });
            if (!count($filtered_cols))
                unset($data[$name]);
        }

        $primaryKey = $this->primaryKey;

        // value of $primaryKey is 'id'
        if (isset($this->$primaryKey))
            return DB::getInstance()
                ->table($this->table)
                ->where($this->primaryKey, '=', $this->primaryKey)
                ->update($data);

        $id = DB::getInstance()
            ->table($this->table)
            ->insertGetId($data);
        $this->$primaryKey = $id; // set id for for new instance

        return $id;
    }
}