<?php

namespace Core\Database\ORM\Relations;

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
}