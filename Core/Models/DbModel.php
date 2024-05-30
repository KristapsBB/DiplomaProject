<?php

namespace DiplomaProject\Core\Models;

use DiplomaProject\Core\Core;

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
    public static function findOneBy(string $field_name, $value): ?static
    {
        $objects = static::find([[$field_name, '=', $value]], limit: 1);

        return $objects[0] ?? null;
    }

    /**
     * @return static[]
     */
    public static function findAll(string $order_by = 'id'): array
    {
        return static::find(order_by: $order_by, limit: null);
    }

    /**
     * ```php
     * <?php
     * $example_condition = [
     *     'AND' => [
     *         ['id', '>', 3]
     *         'OR' => [
     *             ['tender_id', 'IN', [3,7,9]]
     *             ['contract_nature', '=', 'works']
     *         ]
     *     ]
     * ];
     *
     * $example_condition = [
     *     ['id', '>', 3]
     *     ['tender_id', 'IN', [3,7,9]],
     * ];
     * ```
     * @see \DiplomaProject\Core\Models\WhereBuilder
     *
     * @param array $condition
     * @return static[]
     */
    public static function find(
        array $condition = [],
        string $order_by = 'id',
        ?int $limit = null,
    ): array {
        Core::debug('find ' . static::class);

        $db = Core::getCurrentApp()->getDb();
        $table_name = static::$db_table_name;

        $where_string = 'true';
        $query_params = [];
        $limit_string = '';

        if (!empty($condition)) {
            $new_where = static::getNewWhere();
            $where_string = $new_where->build($condition);
            $query_params = $new_where->getQueryParams();
        }

        if (!empty($limit) && $limit > 0) {
            $limit_string = "LIMIT $limit";
        }

        $query_string =
            "SELECT * FROM `{$table_name}`
            WHERE $where_string
            ORDER BY `$order_by` DESC
            $limit_string;";

        $db->execQuery($query_string, $query_params);

        $result_array = $db->getResultAsArray();

        $objects = [];
        foreach ($result_array as $fields) {
            $objects[] = static::getFromArray($fields, true);
        }

        return $objects;
    }

    private static function getNewWhere(): WhereBuilder
    {
        return new WhereBuilder(static::class);
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
    public static function buildDbColumnsFromFields(array $fields)
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

    public static function getDbParamsTempl(int $number_params): string
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
        Core::debug('insert ' . static::class);

        $db = Core::getCurrentApp()->getDb();
        $table_name = static::$db_table_name;

        $params = self::getFieldsForDbQuery($without_id = true);
        $columns = self::buildDbColumnsFromFields($params);
        $columns_templ = self::getDbParamsTempl(count($params));

        $query_string =
            "INSERT INTO `{$table_name}`({$columns})
            VALUES ({$columns_templ});";
        try {
            $db->execQuery($query_string, $params);
        } catch (\mysqli_sql_exception $e) {
            Core::warning($e->getSqlState() . ' : ' . $e->getMessage());
            Core::warning($e->getTraceAsString());
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
     * returns true if the update was successful, and false otherwise;
     * if the updated data is identical to the data in the database, the function returns false
     */
    private function update(): bool
    {
        Core::debug('update ' . static::class);

        $db = Core::getCurrentApp()->getDb();
        $table_name = static::$db_table_name;

        $params = $this->getFieldsForDbQuery();
        $SET = static::buildSET(
            explode(', ', self::buildDbColumnsFromFields($params))
        );
        $params[] = ['type' => 'int', 'value' => $this->id];
        $query_string =
            "UPDATE `{$table_name}`
            SET $SET
            WHERE `id` = ?;";
        try {
            $db->execQuery($query_string, $params);
        } catch (\mysqli_sql_exception $e) {
            Core::warning($e->getSqlState() . ' : ' . $e->getMessage());
            Core::warning($e->getTraceAsString());
            return false;
        }

        return ($db->countAffectedRows() > 0);
    }

    /**
     * saves the object to the database
     */
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

    /**
     * deletes the current object from the database
     */
    public function delete(): bool
    {
        Core::debug('delete ' . static::class);

        return static::deleteBy([['id', '=', $this->id]]);
    }

    /**
     * deletes from the table specified in static::$db_table_name by $condition
     *
     * @see \DiplomaProject\Core\Models\WhereBuilder
     */
    public static function deleteBy(array $condition): bool
    {
        $db = Core::getCurrentApp()->getDb();
        $table_name = static::$db_table_name;

        $new_where = static::getNewWhere();
        $where_string = $new_where->build($condition);
        $query_params = $new_where->getQueryParams();

        $query_string =
            "DELETE FROM `{$table_name}`
            WHERE $where_string;";
        try {
            $db->execQuery($query_string, $query_params);
        } catch (\mysqli_sql_exception $e) {
            Core::warning($e->getSqlState() . ' : ' . $e->getMessage());
            Core::warning($e->getTraceAsString());
            return false;
        }

        return ($db->countAffectedRows() > 0);
    }
}
