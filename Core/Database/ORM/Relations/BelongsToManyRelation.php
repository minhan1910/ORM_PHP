<?php

namespace Core\Database\ORM\Relations;

use Core\Helpers;
use Core\Database\DB;
use Core\Database\ORM\Relations\Relation;

class BelongsToManyRelation extends Relation
{
    protected $type = 'belongs_to_many';
    protected $referenceTable;
    protected $relationTable;
    protected $pivot_table;
    protected $referenceTableForeignKey;
    protected $relationTableForeignKey;
    protected $referenceTableLocalKey;
    protected $relationTableLocalKey;
    protected $pivot_columns = [];


    public function __construct(
        $referenceTable,
        $pivot_table,
        $relationTable,
        $referenceTableForeignKey,
        $relationTableForeignKey,
        $referenceTableLocalKey,
        $relationTableLocalKey
    ) {
        parent::__construct();
        $this->referenceTable = $referenceTable;
        $this->relationTable = $relationTable;
        $this->pivot_table = $pivot_table;
        $this->referenceTableForeignKey = $referenceTableForeignKey;
        $this->relationTableForeignKey = $relationTableForeignKey;
        $this->referenceTableLocalKey = $referenceTableLocalKey;
        $this->relationTableLocalKey = $relationTableLocalKey;
    }

    /**
     * In context users - rateings - posts
     */
    public function initiateConnection()
    {

        // For eager loading and lazy loading
        // Eager loading need not to referenceModel
        if (!$this->connectionInitiated) {

            /**
             *  Connect relationTable to pivotTable
             *  ratings - posts
             * */
            $this->join(
                $this->pivot_table,
                $this->relationTable . '.' . $this->relationTableLocalKey,
                '=',
                $this->pivot_table . '.' . $this->relationTableForeignKey
            );

            /**
             * Connection referenceTable to pivotTable
             */
            $this->join(
                $this->referenceTable, // refenceTable not pivotTable
                $this->referenceTable . '.' . $this->referenceTableLocalKey,
                '=',
                $this->pivot_table . '.' . $this->referenceTableForeignKey,
            );

            $this->connectionInitiated = true;
        }



        // For lazy loading
        $referenceModel = $this->referenceModel;
        if (!empty($referenceModel)) {
            $referenceTableLocalKey = $this->referenceTableLocalKey;
            $this->where(
                $this->pivot_table . '.' . $this->referenceTableForeignKey,
                '=',
                $referenceModel->{$referenceTableLocalKey}
            );
        }

        $this->withPivot($this->referenceTableForeignKey);

        return $this;
    }


    public function withPivot($cols)
    {
        $cols = is_array($cols) ? $cols : [$cols];

        foreach ($cols as $col)
            array_push($this->pivot_columns, $col);

        if (count($this->pivot_columns)) {
            // Chọn lại từ đầu khi select thêm 1 col trong pivot 
            $this->select($this->model->getTable() . '.*');
            foreach ($this->pivot_columns as $col)
                $this->select($this->pivot_table . '.' . $col);
        }

        return $this;
    }

    public function buildRelationDataQuery(mixed $data)
    {

        $ids = array_column($data, $this->referenceTableLocalKey);
        $this->whereIn(
            $this->pivot_table . '.' . $this->referenceTableForeignKey,
            $ids
        );
    }

    public function addRelationData(string $relationName, $data, $relation_data)
    {
        $referenceTableLocalKey = $this->referenceTableLocalKey;
        $referenceTableForeignKey = $this->referenceTableForeignKey;

        foreach ($data as $key => $referenceModel) {
            $filtered_relation_data = array_filter(
                $relation_data,
                function ($relation_obj) use (
                    $referenceTableLocalKey,
                    $referenceTableForeignKey,
                    $referenceModel
                ) {
                    return $referenceModel->{$referenceTableLocalKey} === $relation_obj->pivot->{$referenceTableForeignKey};
                }
            );

            $referenceModel->{$relationName} = $filtered_relation_data;

            // assign again
            $data[$key] = $referenceModel;
        }

        return $data;
    }

    /**
     * @param $data is values of relation_data
     */
    public function addPivotData(array $data)
    {
        if (count($this->pivot_columns)) {
            foreach ($data as $key => $data_object) {
                $pivot = new \stdClass;
                foreach ($this->pivot_columns as $col) {
                    $pivot->{$col} = $data_object->{$col};
                    unset($data_object->{$col});
                }
                $data_object->pivot = clone $pivot;
                $data[$key] = $data_object;
            }
        }
        echo $this->toSql();
        echo '<br>';
        Helpers::formatArray($data);
        exit;
        return $data;
    }

    public function get(): array
    {
        $data = parent::get();

        return $this->addPivotData($data) ?? [];
    }

    public function first(): object
    {
        $model = parent::first();
        $data = $this->addPivotData([$model]);
        return current($data);
    }
}