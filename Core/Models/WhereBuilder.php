<?php

namespace DiplomaProject\Core\Models;

/**
 * Usage example:
 * ```php
 * <?php
 * $condition = [
 *     ['publication_date', '<', '2021-01-01'],
 *     ['contract_nature', 'IN', ['services', 'supplies']],
 * ];
 *
 * $new_where = new WhereBuilder(Tender::class);
 * $where_string = $new_where->build($condition);
 * $query_params = $new_where->getQueryParams();
 *
 * $query_string =
 *     "DELETE FROM `tenders`
 *     WHERE $where_string;";
 *
 * $db->execQuery($query_string, $query_params);
 * ```
 */
class WhereBuilder
{
    public static array $allowed_operators = [
        '=', '<>', '!=',
        '>', '<', '<=', '>=',
        'LIKE', 'NOT LIKE',
        'IN', 'NOT IN'
    ];

    private array $query_params = [];

    public function __construct(
        public string $class
    ) {
    }

    /**
     * input 1:
     * ```php
     * <?php
     * $conditions = [
     *     'AND' => [
     *         ['user_id', '=', 3],
     *         ['tender_id', 'IN', [1,3,5]],
     *     ]
     * ];
     * ```
     *
     * output 1:
     * ```sql
     *(`user_id` = 3) AND (`tender_id` IN [1,3,5])
     * ```
     *
     * input 2:
     * ```php
     * <?php
     * $conditions = [
     *     'AND' => [
     *         ['user_id', '=', 3],
     *         'OR' => [
     *             ['tender_id', '=', 1],
     *             ['tender_id', '=', 3],
     *         ]
     *     ]
     * ];
     * ```
     *
     * output 2:
     * ```sql
     *(`user_id` = 3) AND ((`tender_id` = 1) OR (`tender_id` = 3))
     * ```
     */
    public function build(array $conditions = [], string $logical_operator = 'AND'): string
    {
        $simple_cond_strings = [];
        foreach ($conditions as $key => $condition) {
            if ('AND' === $key || 'OR' === $key) {
                $simple_cond_strings[] = '(' . $this->build($condition, $key) . ')';
            } else {
                $simple_cond_strings[] = $this->buildSimpleCondition(
                    $condition[0],
                    $condition[1],
                    $condition[2]
                );
            }
        }

        return implode($logical_operator, $simple_cond_strings);
    }

    /**
     * returns array in format:
     * ```php
     * <?php
     * $example = [
     *     ['type' => 'string', 'value' => 'abc'],
     *     ['type' => 'int', 'value' => 321],
     * ];
     * ```
     */
    public function getQueryParams(): array
    {
        return $this->query_params;
    }

    private function buildSimpleCondition(
        string $column,
        string $operator,
        $value
    ): string {
        if (false === array_search($operator, static::$allowed_operators)) {
            var_dump($column, $operator, $value);
            throw new \Exception("unknown operator '{$operator}'");
        }

        if (!$this->hasColumn($column)) {
            throw new \Exception("unknown column `{$column}`");
        }

        if (
            ('IN' === $operator || 'NOT IN' === $operator)
            && !is_array($value)
        ) {
            throw new \Exception("the IN operator is used, so '$value' must be an array");
        }

        if ('IN' === $operator || 'NOT IN' === $operator) {
            $templ = $this->getDbParamsTempl(count($value));
            $condition = "`$column` $operator ($templ)";
        } else {
            $condition = "`$column` $operator ?";
        }

        if (is_array($value)) {
            foreach ($value as $val) {
                $this->addQueryParam($column, $val);
            }
        } else {
            $this->addQueryParam($column, $value);
        }

        return "($condition)";
    }

    public function hasColumn(string $column_name): bool
    {
        return (false !== array_search($column_name, $this->class::$db_columns));
    }

    private function addQueryParam(string $column, $value)
    {
        $type = $this->prepareColumnName($column, $value);

        $this->query_params[] = [
            'type'  => $type,
            'value' => $value,
        ];
    }

    /**
     * Sets $value to the correct type and returns the name of the set type.
     */
    public function prepareColumnName(string $field_name, &$value): string
    {
        $rp = new \ReflectionProperty($this->class, $field_name);

        if ($rp->hasType()) {
            $type = $rp->getType()->getName();
            \settype($value, $type);
        } else {
            $type = \gettype($value);
        }

        return $type;
    }

    /**
     * @return string exapmle: "?,?,?,?,?"
     */
    public static function getDbParamsTempl(int $number_params): string
    {
        $params = [];
        for ($i = 0; $i < $number_params; $i++) {
            $params[] = '?';
        }

        return implode(',', $params);
    }
}
