<?php

namespace Core\Database;

use Core\Helpers;

class CommandBuilder
{
    /**
     * select * from users
     * 
     * delete from posts where id = 1
     * 
     * update categories set name = 'laptops' where id = 3
     * 
     * having group by order
     */
    const THREE_ARGUMENTS_FOR_NATIVE_WHERE_CLAUSE = 3;
    const ONE_ARGUMENTS_FOR_CALLBACK_WHERE_CLAUSE = 1;
    private string $table          = '';
    private array  $select         = [];
    private string $where          = '';
    private string $join           = '';
    private string $orderBy         = '';
    private string $groupBy        = '';
    private string $having         = '';
    private string $offset         = '';
    private string $limit          = '';
    private string $commandString  = '';
    // Phải đúng theo thứ tự
    private array $select_command_clauses = [
        'join', 'where', 'groupBy', 'having',
        'orderBy', 'limit', 'offset'
    ];
    private bool $append_and_keyword = false;
    private bool $append_or_keyword = false;

    public function table(string $table): CommandBuilder
    {
        $this->table = $table;

        return $this;
    }

    public function select(string | array $select): CommandBuilder
    {
        if (is_string($select)) {
            if (!in_array($select, $this->select)) {
                array_push($this->select, $select);
            }
        } else if (is_array($select)) {
            $this->select = $select;
        }

        return $this;
    }

    public function orderBy(string $col, $direction = 'asc'): CommandBuilder
    {
        $this->orderBy = 'order by ' . $col . ' ' . $direction;

        return $this;
    }

    public function limit(int $limit): CommandBuilder
    {
        $this->limit = 'limit ' . $limit;

        return $this;
    }

    public function offset(int $offset): CommandBuilder
    {
        $this->offset = 'offset ' . $offset;

        return $this;
    }

    public function where(
        string | \Closure $col,
        $operator = null,
        string | int | float $value = null
    ): CommandBuilder {
        $args = count(func_get_args());

        if (empty($this->where))
            $this->where = ' where ';

        if ($args === self::THREE_ARGUMENTS_FOR_NATIVE_WHERE_CLAUSE) {
            if (
                $this->append_and_keyword ||
                $this->append_or_keyword
            )
                $this->where .= ' and ';
            $this->where .= $col . ' ' . $operator . ' ' . "'$value'" . ' ';
            $this->append_and_keyword = true;
            $this->append_or_keyword = true;
        } else if ($args === self::ONE_ARGUMENTS_FOR_CALLBACK_WHERE_CLAUSE) { // callback
            if ($this->where !== ' where ')
                $this->where .= ' and ';
            $this->where .= ' ( ';
            $this->append_and_keyword = false;
            $this->append_or_keyword = false;
            $col($this);
            $this->where .= ' ) ';
        }

        return $this;
    }

    public function orWhere(
        string | \Closure $col,
        $operator = null,
        string | int | float $value = null
    ): CommandBuilder {
        $args = count(func_get_args());

        if (empty($this->where))
            $this->where = ' where ';

        if ($args === self::THREE_ARGUMENTS_FOR_NATIVE_WHERE_CLAUSE) {
            if (
                $this->append_or_keyword ||
                $this->append_and_keyword
            )
                $this->where .= ' or ';
            $this->where .= $col . ' ' . $operator . ' ' . "'$value'" . ' ';
            $this->append_or_keyword = true;
            $this->append_and_keyword = true;
        } else if ($args === self::ONE_ARGUMENTS_FOR_CALLBACK_WHERE_CLAUSE) {
            if ($this->where !== ' where ')
                $this->where .= ' or ';
            $this->where .= ' ( ';
            $this->append_or_keyword = false;
            $this->append_and_keyword = false;
            $col($this);
            $this->where .= ' ) ';
        }

        return $this;
    }

    /**
     * select *
     * from users
     * where 
     *      name in ('alex', 'danial')
     *      or age in (1, 2, 3)
     */
    public function whereIn(string $col, array $values)
    {
        if (empty($this->where))
            $this->where .= ' where ';

        if (
            $this->append_and_keyword ||
            $this->append_or_keyword
        ) {
            $this->where .= ' and ';
        }

        $this->where .= $col . " in ('" . implode("','", $values) . "')";
        $this->append_and_keyword = true;

        return $this;
    }

    public function orWhereIn(string $col, array $values)
    {
        if (empty($this->where))
            $this->where .= ' where ';

        if (
            $this->append_and_keyword ||
            $this->append_or_keyword
        ) {
            $this->where .= ' or ';
        }

        $this->where .= $col . " in ('" . implode("','", $values) . "')";
        $this->append_or_keyword = true;

        return $this;
    }

    /**
     * $builder
     * $builder
     *  ->table('users')
     *  ->select(['age'])
     *  ->get()
     */
    private function buildSelectCommand(): CommandBuilder
    {
        $command = '';

        $command = count($this->select) === 0 ? $this->table . '.*' : implode(',', $this->select);

        $command = 'select ' . $command . ' from ' . $this->table . " ";

        foreach ($this->select_command_clauses as $clause)
            if (!empty($this->$clause))
                $command .= $this->$clause . ' ';

        // trim the last space lines
        $command = substr($command, 0, strrpos($command, ' '));
        $this->commandString = $command;

        return $this;
    }

    public function join($table, $col1, $operator, $col2)
    {
        $this->join .= ' inner join ' . $table . ' on ' . $col1 . ' ' . $operator . ' ' . $col2 . ' ';

        return $this;
    }

    public function getCommandString(): string
    {
        $this->buildSelectCommand();
        return $this->commandString;
    }

    public function get($class = \stdClass::class): array
    {
        $this->buildSelectCommand();
        $db = DB::getInstance();
        $data = $db->fetch($this->commandString, $class);
        return $data;
    }

    public function avg(string $col): float | int
    {
        $select = ["AVG({$col})"];
        $this->select($select);
        $data = $this->get();
        $obj = current($data);
        $prop = current($select);
        return $obj->$prop;
    }

    public function sum(string $col): float | int
    {
        $select = ["SUM({$col})"];
        $this->select($select);
        $data = $this->get();
        $obj = current($data);
        $prop = current($select);
        return $obj->$prop;
    }

    public function count(string $col): float | int
    {
        $select = ["COUNT({$col})"];
        $this->select($select);
        $data = $this->get();
        $obj = current($data);
        $prop = current($select);
        return $obj->$prop;
    }

    public function min(string $col): float | int
    {
        $select = ["MIN({$col})"];
        $this->select($select);
        $data = $this->get();
        $obj = current($data);
        $prop = current($select);
        return $obj->$prop;
    }

    public function max(string $col): float | int
    {
        $select = ["MAX({$col})"];
        $this->select($select);
        $data = $this->get();
        $obj = current($data);
        $prop = current($select);
        return $obj->$prop;
    }

    public function insert(array $data)
    {
        $this->buildInsertCommand($data);
        return DB::getInstance()->insert($this->commandString);
    }

    public function insertGetId(array $data)
    {
        $this->buildInsertCommand($data);
        return DB::getInstance()->insertGetId($this->commandString);
    }

    /**
     * $data = ['name' => 'An']
     * insert into users (name) values ('An')
     * 
     * $data = [
     *  ['name' => 'An'],
     *  ['name' => 'Hung'],
     * ]
     * 
     * insert into users (name) values ('An'), ('Hung')
     * 
     */
    private function buildInsertCommand(array $data): CommandBuilder
    {
        $cols = [];
        if (Helpers::is_assoc($data)) {
            $cols = array_keys($data);
        } else {
            // vd như người dùng để các col theo đúng thứ tự nên chỉ cần lấy cái thứ 1
            $cols = array_keys($data[0]);
        }

        $command = 'insert into ' . $this->table . ' (' . implode(',', $cols) . ') values ';

        if (Helpers::is_assoc($data)) {
            $command .= "('" . implode("','", array_values($data)) . "')";
        } else {
            foreach ($data as $row)
                $command .= "('" . implode("','", array_values($row)) . "'),";
            $command = substr($command, 0, strrpos($command, ','));
        }
        $this->commandString = $command;

        return $this;
    }


    public function delete(): int
    {
        $this->buildDeleteCommand();
        return DB::getInstance()->delete($this->commandString);
    }

    private function buildDeleteCommand(): CommandBuilder
    {
        $command = 'delete from ' . $this->table;
        if (!empty($this->where))
            $command .= $this->where;
        $this->commandString = $command;

        return $this;
    }

    public function update(array $data): int
    {
        $this->buildUpdateCommand($data);
        return DB::getInstance()->update($this->commandString);
    }

    private function buildUpdateCommand(array $data): CommandBuilder
    {
        $command = 'update ' . $this->table . ' set ';
        foreach ($data as $col => $value) {
            $command .= $col . "= '" . $value . "', ";
        }
        $command = substr($command, 0, strrpos($command, ','));

        if (!empty($this->where))
            $command .= $this->where;
        $this->commandString = $command;

        return $this;
    }
}