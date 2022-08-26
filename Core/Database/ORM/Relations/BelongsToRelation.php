<?php

namespace Core\Database\ORM\Relations;

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
        }
        return $this;
    }
}