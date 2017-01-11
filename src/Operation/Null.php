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
 * This class represents a null database operation.
 */
class PHPUnit_Extensions_Database_Operation_Null implements PHPUnit_Extensions_Database_Operation_IDatabaseOperation
{
    public function execute(IConnection $connection, PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
    {
        /* do nothing */
    }
}
