<?php

namespace Core\Database\ORM;

use Core\Database\CommandBuilder;
use Core\Helpers;

class Builder
{
    protected $commandBuilder; // commandBuilder
    protected $model;
    protected $relations = [];

    // Can use IoC or Dependency injection cũng tùy
    public function __construct()
    {
        $this->commandBuilder = new CommandBuilder;
    }

    /**
     * Simulate this metod into command side:
     * 
     * User::with(['profile', 'posts' => function($posts) {
     *      $posts->where('created_at', '>', '2020-09-23');
     * }])->get();
     */
    public function with(array $relations = []): Builder
    {
        foreach ($relations as $relationKeyOrName => $relationNameOrClosure) {
            if (is_string($relationNameOrClosure)) {
                // Call to relationship of model
                $relation = call_user_func_array([$this->model, $relationNameOrClosure], []);
                $this->relations[$relationNameOrClosure] = $relation;
            } else {
                $relation = call_user_func_array([$this->model, $relationKeyOrName], []);
                $relationNameOrClosure($relation);
                $this->relations[$relationKeyOrName] = $relation;
            }
        }

        // -- Not returned relation is relationModel 
        // -- returned $this is referenceModel
        return $this;
    }

    public function model($model): Builder
    {
        $this->model = $model;
        return $this;
    }

    /** 
     * 
     * Nhức Đầu :>
     */
    public function get(): array
    {
        $classModelName = get_class($this->model); // get the class name of instance

        // $data contains difference of objects of referenceModel
        $data = $this
            ->commandBuilder
            ->table($this->getModelTable())
            ->get($classModelName);



        // For Eager loading and manipulating data returned
        if (count($data) && count($this->relations)) {
            foreach ($this->relations as $relationName => $relation) {
                // Step 1: 
                $relation->buildRelationDataQuery($data);

                // Step 2:
                // after filter all ids of referenceModel
                $relation_data = $relation->get();

                // Step 3: 
                // Maybe not retuern array data from addRelationData
                $data = $relation->addRelationData($relationName, $data, $relation_data);
            }
        }

        return $data;
    }

    public function where(): Builder
    {
        $this->invokeCbMethod('where', func_get_args());
        return $this;
    }

    public function orWhere(): Builder
    {
        $this->invokeCbMethod('orWhere', func_get_args());
        return $this;
    }

    public function whereIn(): Builder
    {
        $this->invokeCbMethod('whereIn', func_get_args());
        return $this;
    }

    public function orWhereIn(): Builder
    {
        $this->invokeCbMethod('orWhereIn', func_get_args());
        return $this;
    }

    public function select(): Builder
    {
        $this->invokeCbMethod('select', func_get_args());
        return $this;
    }

    public function offset(): Builder
    {
        $this->invokeCbMethod('offset', func_get_args());
        return $this;
    }

    public function limit(): Builder
    {
        $this->invokeCbMethod('limit', func_get_args());
        return $this;
    }

    public function orderBy(): Builder
    {
        $this->invokeCbMethod('orderBy', func_get_args());
        return $this;
    }

    public function join(): Builder
    {
        $this->invokeCbMethod('join', func_get_args());
        return $this;
    }

    public function toSql()
    {
        return $this
            ->commandBuilder
            ->table($this->getModelTable())
            ->getCommandString();
    }

    public function avg(): int | float
    {
        $this
            ->commandBuilder
            ->table($this->getModelTable());

        return $this->invokeCbMethod('avg', func_get_args());
    }

    public function sum(): int | float
    {
        $this
            ->commandBuilder
            ->table($this->getModelTable());

        return $this->invokeCbMethod('sum', func_get_args());
    }

    public function count(): int | float
    {
        $this
            ->commandBuilder
            ->table($this->getModelTable());

        return $this->invokeCbMethod('count', func_get_args());
    }

    public function min(): int | float
    {
        $this
            ->commandBuilder
            ->table($this->getModelTable());

        return $this->invokeCbMethod('min', func_get_args());
    }

    public function max(): int | float
    {

        $this
            ->commandBuilder
            ->table($this->getModelTable());

        return $this->invokeCbMethod('max', func_get_args());
    }

    private function invokeCbMethod(string $methodName, array $args): mixed
    {
        return call_user_func_array([$this->commandBuilder, $methodName], $args);
    }

    /** first, find, create */
    public function first(): object | null
    {
        $this->limit(1);
        $data = $this->get();

        // Actually 1 row
        return !empty($data) ? current($data) : null;
    }

    public function find($id): object | null
    {
        $primaryKey = $this
            ->model
            ->getPrimaryKey();

        return $this
            ->where($primaryKey, '=', $id)
            ->first();
    }

    public function create(array $data): object | null
    {
        $new_id = $this
            ->commandBuilder
            ->table($this->getModelTable())
            ->insertGetId($data);

        // For hasOne relationship
        if (array_key_exists('id', $data) && isset($data['id']))
            $new_id = $data['id'];

        return call_user_func_array([$this, 'find'], [$new_id]);
    }

    public function delete()
    {
        return $this
            ->commandBuilder
            ->table($this->getModelTable())
            ->delete();
    }

    public function update(array $data)
    {
        return $this
            ->commandBuilder
            ->table($this->getModelTable())
            ->update($data);
    }

    public function getModelTable(): string
    {
        return $this->model->getTable();
    }
}