<?php
/*
 * This file is part of DBUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\DbUnit\Database\IConnection;

/**
 * Updates the rows in a given dataset using primary key columns.
 */
class PHPUnit_Extensions_Database_Operation_Update extends PHPUnit_Extensions_Database_Operation_RowBased
{
    protected $operationName = 'UPDATE';

    protected function buildOperationQuery(PHPUnit_Extensions_Database_DataSet_ITableMetaData $databaseTableMetaData, PHPUnit_Extensions_Database_DataSet_ITable $table, IConnection $connection)
    {
        $keys           = $databaseTableMetaData->getPrimaryKeys();
        $columns        = $table->getTableMetaData()->getColumns();
        $whereStatement = 'WHERE ' . implode(' AND ', $this->buildPreparedColumnArray($keys, $connection));
        $setStatement   = 'SET ' . implode(', ', $this->buildPreparedColumnArray($columns, $connection));

        $query = "
            UPDATE {$connection->quoteSchemaObject($table->getTableMetaData()->getTableName())}
            {$setStatement}
            {$whereStatement}
        ";

        return $query;
    }

    protected function buildOperationArguments(PHPUnit_Extensions_Database_DataSet_ITableMetaData $databaseTableMetaData, PHPUnit_Extensions_Database_DataSet_ITable $table, $row)
    {
        $args = [];
        foreach ($table->getTableMetaData()->getColumns() as $columnName) {
            $args[] = $table->getValue($row, $columnName);
        }

        foreach ($databaseTableMetaData->getPrimaryKeys() as $columnName) {
            $args[] = $table->getValue($row, $columnName);
        }

        return $args;
    }

    protected function disablePrimaryKeys(PHPUnit_Extensions_Database_DataSet_ITableMetaData $databaseTableMetaData, PHPUnit_Extensions_Database_DataSet_ITable $table, IConnection $connection)
    {
        if (count($databaseTableMetaData->getPrimaryKeys())) {
            return true;
        }

        return false;
    }
}
