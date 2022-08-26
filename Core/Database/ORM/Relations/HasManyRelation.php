<?php

namespace Core\Database\ORM\Relations;

class HasManyRelation extends Relation
{
    protected $type = 'has_many';
    protected $referenceTable;
    protected $foreignTable;
    protected $foreignKey;
    protected $localKey;

    public function __construct(
        $referenceTable,
        $foreignTable,
        $foreignKey,
        $localKey
    ) {
        // For new CommandBuilder in Builder class
        parent::__construct();
        $this->referenceTable = $referenceTable;
        $this->foreignTable = $foreignTable;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

    public function initiateConnection()
    {
        $localKey = $this->localKey;
        $referenceModel = $this->referenceModel; // object in Model
        if (!$this->connectionInitiated && !empty($referenceModel)) {
            $this->where(
                $this->foreignTable . '.' . $this->foreignKey,
                '=',
                // user object and localKey is id column name
                $referenceModel->{$localKey}
            );
        }

        return $this;
    }
}