<?php
/*
 * This file is part of DBUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\DbUnit\Database;

use PHPUnit\DbUnit\InvalidArgumentException;
use PHPUnit\DbUnit\RuntimeException;
use PHPUnit_Extensions_Database_DataSet_AbstractDataSet;
use PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData;
use PHPUnit_Extensions_Database_DataSet_ITableMetaData;
use PHPUnit_Extensions_Database_DB_Table;
use PHPUnit_Extensions_Database_DB_TableIterator;

/**
 * Provides access to a database instance as a data set.
 */
class DataSet extends PHPUnit_Extensions_Database_DataSet_AbstractDataSet
{
    /**
     * An array of ITable objects.
     *
     * @var array
     */
    protected $tables = [];

    /**
     * The database connection this dataset is using.
     *
     * @var IConnection
     */
    protected $databaseConnection;

    /**
     * Creates a new dataset using the given database connection.
     *
     * @param IConnection $databaseConnection
     */
    public function __construct(IConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }

    /**
     * Creates the query necessary to pull all of the data from a table.
     *
     * @param  PHPUnit_Extensions_Database_DataSet_ITableMetaData $tableMetaData
     * @return string
     */
    public static function buildTableSelect(PHPUnit_Extensions_Database_DataSet_ITableMetaData $tableMetaData, IConnection $databaseConnection = null)
    {
        if ($tableMetaData->getTableName() == '') {
            $e = new RuntimeException('Empty Table Name');
            echo $e->getTraceAsString();
            throw $e;
        }

        $columns = $tableMetaData->getColumns();
        if ($databaseConnection) {
            $columns = array_map([$databaseConnection, 'quoteSchemaObject'], $columns);
        }
        $columnList = implode(', ', $columns);

        if ($databaseConnection) {
            $tableName = $databaseConnection->quoteSchemaObject($tableMetaData->getTableName());
        } else {
            $tableName = $tableMetaData->getTableName();
        }

        $primaryKeys = $tableMetaData->getPrimaryKeys();
        if ($databaseConnection) {
            $primaryKeys = array_map([$databaseConnection, 'quoteSchemaObject'], $primaryKeys);
        }
        if (count($primaryKeys)) {
            $orderBy = 'ORDER BY ' . implode(' ASC, ', $primaryKeys) . ' ASC';
        } else {
            $orderBy = '';
        }

        return "SELECT {$columnList} FROM {$tableName} {$orderBy}";
    }

    /**
     * Creates an iterator over the tables in the data set. If $reverse is
     * true a reverse iterator will be returned.
     *
     * @param  bool $reverse
     * @return PHPUnit_Extensions_Database_DB_TableIterator
     */
    protected function createIterator($reverse = false)
    {
        return new PHPUnit_Extensions_Database_DB_TableIterator($this->getTableNames(), $this, $reverse);
    }

    /**
     * Returns a table object for the given table.
     *
     * @param  string $tableName
     * @return PHPUnit_Extensions_Database_DB_Table
     */
    public function getTable($tableName)
    {
        if (!in_array($tableName, $this->getTableNames())) {
            throw new InvalidArgumentException("$tableName is not a table in the current database.");
        }

        if (empty($this->tables[$tableName])) {
            $this->tables[$tableName] = new PHPUnit_Extensions_Database_DB_Table($this->getTableMetaData($tableName), $this->databaseConnection);
        }

        return $this->tables[$tableName];
    }

    /**
     * Returns a table meta data object for the given table.
     *
     * @param  string $tableName
     * @return PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData
     */
    public function getTableMetaData($tableName)
    {
        return new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData($tableName, $this->databaseConnection->getMetaData()->getTableColumns($tableName), $this->databaseConnection->getMetaData()->getTablePrimaryKeys($tableName));
    }

    /**
     * Returns a list of table names for the database
     *
     * @return array
     */
    public function getTableNames()
    {
        return $this->databaseConnection->getMetaData()->getTableNames();
    }
}
