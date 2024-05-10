<?php

namespace DiplomaProject\Core;

/**
 * all find, update, insert, etc. methods work only with fields specified in static::$db_columns
 */
class DbModel
{
    public static string $db_table_name = 'models';
    public static array $db_columns = [
        'id',
    ];

    /**
     * default primary key
     */
    public int $id;

    /**
     * if the value is set to "true", then INSERT will be used to save the Model
     */
    private bool $is_saved_in_db = false;

    /**
     * finds and returns an object by one field using the '=' operator
     */
    public static function findOneBy(string $field_name, string $value, string $type = 'string'): ?static
    {
        $db = Core::getCurrentApp()->getDb();
        $table_name = static::$db_table_name;

        $query_string = "SELECT * FROM `{$table_name}` WHERE `{$field_name}`=? LIMIT 1;";
        $db->execQuery($query_string, [['type' => $type, 'value' => $value]]);

        $result_array = $db->getResultAsArray();

        if (count($result_array) === 0) {
            return null;
        }

        return static::getFromArray($result_array[0], true);
    }

    public static function findAll($order_by = 'id'): array
    {
        $db = Core::getCurrentApp()->getDb();
        $table_name = static::$db_table_name;

        $query_string = "SELECT * FROM {$table_name} ORDER BY `$order_by` DESC";
        $db->execQuery($query_string);

        $result_array = $db->getResultAsArray();

        return $result_array;
    }

    /**
     * Creates a new static() object and fills its properties with data from $fields;
     * Does not validate the input data
     */
    private static function getFromArray(array $fields, bool $is_saved_in_db = false): static
    {
        $user = new static();

        foreach ($fields as $key => $value) {
            if (!property_exists($user, $key)) {
                continue;
            }

            $user->{$key} = $value;
        }

        if ($is_saved_in_db) {
            $user->setSavedInDb();
        }

        return $user;
    }

    /**
     * returns the object fields in the format required for DataBase->execQuery()
     */
    private function getFieldsForDbQuery(bool $without_id = false): array
    {
        $fields = [];

        foreach (static::$db_columns as $db_column) {
            if ($without_id && 'id' === $db_column) {
                continue;
            }

            $rp = new \ReflectionProperty(static::class, $db_column);

            if (!$rp->hasType()) {
                continue;
            }

            switch ($type = $rp->getType()->getName()) {
                case 'int':
                case 'integer':
                case 'float':
                case 'string':
                    $fields[] = [
                        'type' => $type,
                        'value' => $this->{$db_column},
                        'name' => $db_column,
                    ];
                    break;
            }
        }

        return $fields;
    }

    /**
     * builds a query fragment from the passed fields,
     * returns a string in the format:
     * ```sql
     *  "`column1`, `column2`, `column3`"
     * ```
     */
    public function buildDbColumnsFromFields(array $fields)
    {
        return implode(
            ', ',
            array_map(
                function ($value) {
                    return "`{$value}`";
                },
                \array_column($fields, 'name')
            )
        );
    }

    public function getDbParamsTempl(int $number_params): string
    {
        $params = [];
        for ($i = 0; $i < $number_params; $i++) {
            $params[] = '?';
        }

        return implode(', ', $params);
    }

    /**
     * builds a query fragment:
     * ```sql
     *  `column1` = ?,
     *  `column2` = ?,
     *  `column3` = ?
     * ```
     */
    public static function buildSET(array $columns): string
    {
        $sets = [];

        foreach ($columns as $column) {
            $sets[] = "{$column} = ?";
        }

        return implode(', ', $sets);
    }

    /**
     * returns true if the insertion was successful, and false otherwise
     */
    private function insert(): bool
    {
        $db = Core::getCurrentApp()->getDb();
        $table_name = static::$db_table_name;

        $params = $this->getFieldsForDbQuery($without_id = true);
        $columns = $this->buildDbColumnsFromFields($params);
        $columns_templ = $this->getDbParamsTempl(count($params));

        $query_string =
            "INSERT INTO `{$table_name}`({$columns})
            VALUES ({$columns_templ});";
        try {
            $db->execQuery($query_string, $params);
        } catch (\mysqli_sql_exception $e) {
            return false;
        }

        $insert_id = $db->getInsertId();

        if (!$insert_id) {
            return false;
        }

        $this->id = $insert_id;
        return true;
    }

    /**
     * updates all columns specified in static::$bd_columns,
     * returns true if the update was successful, and false otherwise
     */
    private function update(): bool
    {
        $db = Core::getCurrentApp()->getDb();
        $table_name = static::$db_table_name;

        $params = $this->getFieldsForDbQuery();
        $SET = static::buildSET(
            explode(', ', $this->buildDbColumnsFromFields($params))
        );
        $params[] = ['type' => 'int', 'value' => $this->id];

        $query_string =
            "UPDATE `{$table_name}`
            SET $SET
            WHERE `id` = ?;";
        try {
            $db->execQuery($query_string, $params);
        } catch (\mysqli_sql_exception $e) {
            return false;
        }

        return ($db->countAffectedRows() > 0);
    }

    public function save(): bool
    {
        $result = false;

        if (!$this->is_saved_in_db) {
            $result = $this->insert();
        } else {
            $result = $this->update();
        }

        if (!$result) {
            return false;
        }

        $this->is_saved_in_db = true;
        return true;
    }

    public function isSavedInDb(): bool
    {
        return $this->is_saved_in_db;
    }

    private function setSavedInDb()
    {
        $this->is_saved_in_db = true;
    }
}
