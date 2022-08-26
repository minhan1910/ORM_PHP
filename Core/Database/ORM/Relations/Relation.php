<?php

namespace Core\Database\ORM\Relations;

use Core\Database\ORM\Builder;

abstract class Relation extends Builder
{
    protected $type                 = '';
    protected $connectionInitiated  = false;
    protected $referenceModel       = null;

    public function getReferenceModel()
    {
        return $this->referenceModel;
    }

    public function referenceModel($referenceModel): Relation
    {
        $this->referenceModel = $referenceModel;

        return $this;
    }

    public abstract function initiateConnection();
}