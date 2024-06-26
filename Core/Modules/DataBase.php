<?php

namespace DiplomaProject\Core\Modules;

use DiplomaProject\Core\Core;

class DataBase
{
    private array $config = [
        'hostname' => '',
        'username' => '',
        'password' => '',
        'database' => '',
    ];

    private \mysqli $connection;
    private \mysqli_stmt $last_prepared_query;
    private \mysqli_result | false $last_query_result_obj;
    private ?array $last_query_result = null;

    public function configure(array $params)
    {
        foreach ($this->config as $field_name => $value) {
            if (!array_key_exists($field_name, $params) || empty($params[$field_name])) {
                throw new \Exception("\$db_config['{$field_name}'] is empty");
            }

            $this->config[$field_name] = $params[$field_name];
        }

        $result = $this->connect(
            $this->config['hostname'],
            $this->config['username'],
            $this->config['password'],
            $this->config['database']
        );

        if (!$result) {
            throw new \RuntimeException('Database connection error');
        }

        Core::info(static::class . ' module is configured');
    }

    /**
     * initializes the connection to the database
     */
    public function connect(string $hostname, string $username, string $password, string $database): bool
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->connection = new \mysqli($hostname, $username, $password, $database);
            $this->connection->set_charset('utf8mb4');
        } catch (\Throwable $th) {
            Core::error($th->getMessage());
            Core::error($th->getTraceAsString());
            return false;
        }

        return true;
    }

    public static function getMysqlType(string $php_type): string
    {
        $mysql_type = '';

        switch ($php_type) {
            case 'int':
            case 'integer':
                $mysql_type .= 'i';
                break;
            case 'float':
            case 'double':
            case 'decimal':
                $mysql_type .= 'd';
                break;
            case 'string':
                $mysql_type .= 's';
                break;
            case 'binary':
                $mysql_type .= 'b';
                break;
        }

        if (!empty($mysql_type)) {
            return $mysql_type;
        }

        throw new \Exception('Unknown data type');
    }

    /**
     * executes the prepared query to the database
     *
     * @param string $query_string example: INSERT INTO `students`(`id`, `name`) VALUES (1,'John');
     * @param array $fields example: [0 => ['value' => 1, 'type' => 'string'], 1 => ...]
     */
    public function execQuery(string $query_string, array $fields = []): bool
    {
        Core::debug($query_string);
        Core::debug('query_params: ' . print_r($fields, true));

        $query = $this->connection->prepare($query_string);

        if (!empty($fields)) {
            $types = '';
            $values = [];
            foreach ($fields as $field) {
                $types .= self::getMysqlType($field['type']);
                $values[] = $field['value'];
            }

            $query->bind_param($types, ...$values);
        }

        $is_complete = $query->execute();

        $this->last_query_result = null;
        $this->last_prepared_query = $query;
        $this->last_query_result_obj = $query->get_result();

        return $is_complete;
    }

    /**
     * fetches the result rows of the last query from the database
     * and returns they as an array of associative
     */
    public function getResultAsArray(): array
    {
        if (null === $this->last_query_result) {
            $this->last_query_result = $this->last_query_result_obj->fetch_all(MYSQLI_ASSOC);
        }

        return $this->last_query_result;
    }

    /**
     * returns the ID of the row in the database
     * received as a result of the last successful INSERT operation
     */
    public function getInsertId(): int
    {
        return $this->last_prepared_query->insert_id ?? 0;
    }

    /**
     * returns the number of rows affected by the last operation (UPDATE, INSERT, etc)
     */
    public function countAffectedRows(): int
    {
        return $this->last_prepared_query->affected_rows ?? 0;
    }
}
