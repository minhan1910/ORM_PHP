<?php

namespace Core\Database\ORM\Relations;

use Core\Helpers;

class BelongsToRelation extends Relation
{

    protected $type = 'belongs_to';
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
            $foreignKey = $this->foreignKey;
            $this->where(
                $this->relationTable . '.' . $this->localKey,
                '=',
                $referenceModel->{$foreignKey}
            );
            $this->connectionInitiated = true;
        }
        return $this;
    }

    /**
     * @param $data is values of referenceModel
     * 
     */
    public function buildRelationDataQuery($data)
    {
        $ids = array_column($data, $this->localKey);
        $this->whereIn(
            $this->relationTable . '.' . $this->localKey,
            $ids
        );
    }

    // user::with(['posts']) 
    // user: Post::class, 'user_id', 'id'
    public function addRelationData(string $relationName, $data, $relation_data)
    {
        $localKey = $this->localKey;
        $foreignKey = $this->foreignKey;
        foreach ($data as $key => $referenceModel) {
            $filtered_relation_data = array_filter(
                $relation_data,
                function ($relation_data_object) use ($localKey, $foreignKey, $referenceModel) {
                    return $referenceModel->{$localKey} === $relation_data_object->{$foreignKey};
                }
            );

            if (count($filtered_relation_data))
                $referenceModel->{$relationName} = current($filtered_relation_data);
            // Không cần assign lại vì nó đã là object => reference
            // $data[$key] = $referenceModel;
        }

        return $data;
    }
}