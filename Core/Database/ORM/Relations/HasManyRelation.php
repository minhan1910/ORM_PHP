<?php

namespace Core\Database\ORM\Relations;

class HasManyRelation extends Relation
{
    protected $type = 'has_many';
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
        // For new CommandBuilder in Builder class
        parent::__construct();
        $this->referenceTable = $referenceTable;
        $this->relationTable = $relationTable;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

    public function initiateConnection()
    {
        $referenceModel = $this->referenceModel; // object in Model
        if (!$this->connectionInitiated && !empty($referenceModel)) {
            $localKey = $this->localKey;
            $this->where(
                $this->relationTable . '.' . $this->localKey,
                '=',
                // user object and localKey is id column name
                $referenceModel->{$localKey}
            );
            $this->connectionInitiated = true;
        }

        return $this;
    }
}