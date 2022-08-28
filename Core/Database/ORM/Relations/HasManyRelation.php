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
                $this->relationTable . '.' . $this->foreignKey,
                '=',
                // user object and localKey is id column name
                $referenceModel->{$localKey}
            );
            $this->connectionInitiated = true;
        }

        return $this;
    }

    public function buildRelationDataQuery($data)
    {
        $ids = array_column($data, $this->localKey); // get values of record set from DB
        // eg: $this->relationTable is posts and $this->localKey is userId
        // => posts.userId IN (1, 2, 3)
        $this->whereIn($this->relationTable . '.' . $this->foreignKey, $ids);

        return $this;
    }

    /**
     * Assign relationData into object referenceModel
     */
    public function addRelationData($relationName, $data, $relation_data)
    {
        /**
         * users posts $user->id localKey $post->userId foreignKey
         */
        $localKey = $this->localKey;
        $foreignKey = $this->foreignKey;
        foreach ($data as $key => $referenceModel) {
            $filtred_relation_data = array_filter(
                $relation_data,
                function ($relation_data_object) use (
                    $foreignKey,
                    $localKey,
                    $referenceModel
                ) {
                    return $referenceModel->{$localKey} == $relation_data_object->{$foreignKey};
                }
            );

            // assign into $referenceModel object  with relationName
            // is User has Posts -> rassign array
            $referenceModel->{$relationName} = $filtred_relation_data;

            // Users have each User has Posts
            // $data is sequential array so with $key for modified it.
            $data[$key] = $referenceModel;
        }

        return $data;
    }

    /**
     * Why does I override this method ?
     * 
     * Because 
     * $user = User::find(1);
     * $post = $user->posts()->create([
     *      'title' => 'An first post',
     * ]);
     * -> $post must user's id and not add field user's id into 
     * create method.
     */
    public function create(array $data): object | null
    {
        $foreignKey = $this->foreignKey;
        $referenceModel = $this->referenceModel;
        $localKey = $this->localKey;
        // Attach user's id into referenceModel liek $user
        // mà id nằm ở cột post và giữ  trong fk_user_id
        $data[$foreignKey] = $referenceModel->{$localKey};

        return parent::create($data);
    }
}