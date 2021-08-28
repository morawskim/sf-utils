<?php

namespace mmo\sf\Doctrine;

use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

/**
 * @link http://kamiladryjanek.com/ignore-entity-or-table-when-running-doctrine2-schema-update-command/
 */
class IgnoreSchemaTablesListener
{
    /**
     * @var string[]
     */
    private $ignoredTables;

    public function __construct(array $ignoredTables = [])
    {
        $this->ignoredTables = $ignoredTables;
    }

    /**
     * Remove ignored tables from Schema
     *
     * @param GenerateSchemaEventArgs $args
     */
    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $schema = $args->getSchema();
        $ignoredTables = $this->ignoredTables;
        foreach ($schema->getTableNames() as $tableName) {
            if (in_array($tableName, $ignoredTables, true)) {
                $schema->dropTable($tableName);
            }
        }
    }
}
