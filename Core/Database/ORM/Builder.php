<?php

namespace Core\Database\ORM;

use Core\Database\CommandBuilder;

class Builder
{
    protected $commandBuilder; // commandBuilder
    protected $model;

    // Can use IoC or Dependency injection cũng tùy
    public function __construct()
    {
        $this->commandBuilder = new CommandBuilder;
    }

    public function model($model): Builder
    {
        $this->model = $model;
        return $this;
    }

    public function get(): array
    {
        $classModelName = get_class($this->model); // get the class name of instance

        return $this
            ->commandBuilder
            ->table($this->getModelTable())
            ->get($classModelName);
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
    public function first(): object
    {
        $this->limit(1);
        $data = $this->get();

        // Actually 1 row
        return count($data) > 0 ? current($data) : null;
    }

    public function find($id): object
    {
        $primaryKey = $this
            ->model
            ->getPrimaryKey();

        return $this
            ->where($primaryKey, '=', $id)
            ->first();
    }

    public function create(array $data)
    {
        $new_id = $this
            ->commandBuilder
            ->table($this->getModelTable())
            ->insertGetId($data);

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

    public function getModelTable()
    {
        return $this->model->getTable();
    }
}