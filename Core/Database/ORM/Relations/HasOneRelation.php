<?php

namespace Core\Database\ORM\Relations;

use Core\Helpers;

class HasOneRelation extends Relation
{
    protected $type = 'one_has';
    protected $referenceTable;
    protected $relationTable;
    protected $foreignKey;
    protected $localKey;

    public function __construct(
        $referenceTable,
        $relationTable,
        $foreignKey,
        $localKey
    ) {
        parent::__construct();
        $this->referenceTable = $referenceTable;
        $this->relationTable = $relationTable;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

    public function initiateConnection()
    {
        $referenceModel = $this->referenceModel;

        if (!$this->connectionInitiated && !empty($referenceModel)) {
            $localKey = $this->localKey;
            $this->where(
                $this->relationTable . '.' . $this->foreignKey,
                '=',
                $referenceModel->{$localKey}
            );
            $this->connectionInitiated = true;
        }

        return $this;
    }


    /**
     * @param $data is values of referenceModel not relationModel
     * @var $this->foreignKey is id or primary key of relationModel
     * 
     * In relationTable like profiles has query is:
     * SELECT profiles.* FROM profiles WHHERE profiles.id = ($ids)
     * 
     * $ids is values of referenceModel have many id for building whereIn query
     */

    public function buildRelationDataQuery(mixed $data)
    {
        $ids = array_column($data, $this->localKey);

        $this->whereIn(
            $this->relationTable . '.' . $this->foreignKey,
            $ids
        );
    }

    /**
     * @param $relationName is relationName
     * @param $data is values of referenceModel when use get() method
     * @param $relation_data is values of relationModel 
     * like posts in context : each post of users / when use get() method
     */
    public function addRelationData(string $relationName, $data, $relation_data)
    {
        $foreignKey = $this->foreignKey;
        $localKey = $this->localKey;
        /**
         * maybe O(n^2)
         */
        foreach ($data as $key => $referenceModel) {
            $filtered_relation_data = array_filter(
                $relation_data,
                function ($relation_data_object) use (
                    $foreignKey,
                    $localKey,
                    $referenceModel
                ) {
                    return $referenceModel->{$localKey} === $relation_data_object->{$foreignKey};
                }
            );
            // By hasOne so we can get one element after filtered 
            // assign object not array because hasOne relationship
            if (count($filtered_relation_data))
                $referenceModel->{$relationName} = current($filtered_relation_data);

            // assign referenceModel after manipulate or it can reference not assign it
            $data[$key] = $referenceModel;
        }

        return $data;
    }

    public function create(array $data): object | null
    {
        $foreignKey = $this->foreignKey;
        $localKey = $this->localKey;
        $referenceModel = $this->referenceModel;
        $data[$foreignKey] = $referenceModel->{$localKey};

        return parent::create($data);
    }
}