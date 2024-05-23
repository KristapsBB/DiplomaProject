<?php

namespace DiplomaProject\Core\Models;

use DiplomaProject\Core\Core;

class Migration extends DbModel
{
    public static string $db_table_name = 'migrations';
    public static array $db_columns = [
        'id',
        'name',
    ];

    public function __construct(public string $name = '')
    {
    }

    public static function createNewName($prefix = 'migration'): string
    {
        return $prefix . '-' . date('YmdHis');
    }

    public static function initTable()
    {
        $db = Core::getCurrentApp()->getDb();

        $db->execQuery(
            "CREATE TABLE IF NOT EXISTS `migrations` (
                `id` INT NOT NULL AUTO_INCREMENT ,
                `name` VARCHAR(24) NOT NULL ,
                PRIMARY KEY (`id`),
                UNIQUE (`name`)
            ) ENGINE = InnoDB;"
        );
    }

    /**
     * @param string[] $migrations
     */
    public static function upAll(array $migrations = [])
    {
        print_r("start migrating\n");
        $db = Core::getCurrentApp()->getDb();

        $applied_migrations = array_column(Migration::findAll(), 'name');
        sort($applied_migrations);

        $new_migrations = array_diff(
            array_keys($migrations),
            $applied_migrations
        );

        $sql_comment_pattern = "/\#.*/i";
        $comments = [];

        foreach ($new_migrations as $migration_name) {
            print_r("up $migration_name");

            $sql = $migrations[$migration_name];

            /**
             * delete comments
             */
            preg_match_all($sql_comment_pattern, $sql, $comments);
            for ($i = 0; $i < count($comments[0]); $i++) {
                $sql = preg_replace($sql_comment_pattern, '', $sql);
            }

            foreach (explode(';', $sql) as $query) {
                $query = trim($query);

                if (empty($query)) {
                    continue;
                }

                try {
                    $db->execQuery($query);
                } catch (\Exception $e) {
                    Core::error($e->getMessage());
                    Core::error($e->getTraceAsString());
                    print_r(" fail\n");
                    return;
                }
            }

            (new Migration($migration_name))->save();
            print_r(" ok\n");
        }

        print_r("finish migrating\n");
    }
}
