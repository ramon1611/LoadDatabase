<?php
/**
 * File: loadDatabase.class.php
 * Project: libs
 * File Created: Wednesday, 31st January 2018 9:54:48 am
 * Author: ramon1611
 * -----
 * Last Modified: Thursday, 1st February 2018 4:44:02 pm
 * Modified By: ramon1611
 */

/**
 * @namespace ramon1611\Libs\Ticket
 */
namespace ramon1611\Libs\Ticket;

/**
 * Database loader class for Ticketsystem
 * 
 * @api
 * @package LoadDatabase
 */
class LoadDatabase {
    /**
     * @var array Constant for loading all tables of database
     */
    const ALL_TABLES = [ '*' ];

    /**
     * @var object|null $_db Instance of a Yadal class
     */
    private $_db = NULL;
    /**
     * @var object|null $_query Instance of a SQLQueryBuilder class
     */
    private $_query = NULL;
    /**
     * @var string|null $_defaultOp Default operator for conditions
     */
    private $_defaultOp = NULL;

    /**
     * Constructor
     * 
     * @param mixed $dbObject Instance of a Yadal class. If NULL: Instance in $GLOBALS['db'] is used if present. Default is NULL
     * @param mixed $queryGenerator Instance of an SQLQueryBuilder class. If NULL: Instance in $GLOBALS['query'] is used if present. Default is NULL
     * @param string $defaultOperator Sets the default operator for conditions. Default is 'AND'
     * @return void
     */
    public function __construct( $dbObject = NULL, $queryGenerator = NULL, string $defaultOperator = 'AND' ) {
        if ( isset( $dbObject ) && is_object( $dbObject ) )
            $this->_db = $dbObject;
        elseif ( isset( $GLOBALS['db'] ) )
            $this->_db = $GLOBALS['db'];
        else
            trigger_error( 'No Database Object provided!', E_USER_ERROR );

        if ( isset( $queryGenerator ) && is_object( $queryGenerator ) )
            $this->_query = $queryGenerator;
        elseif ( isset( $GLOBALS['query'] ) )
            $this->_query = $GLOBALS['query'];
        else
            trigger_error( 'No Query-Generator Object provided!', E_USER_ERROR );

        $this->_defaultOp = $defaultOperator;
    }

    /**
     * Loads the given Tables from Database and returns them
     * 
     * @param array $dbTables Array of table names to load. Default is LoadDatabase::ALL_TABLES
     * @return array
     */
    public function requireTables( array $dbTables = self::ALL_TABLES ) {
        $requiredTables = NULL;

        if ( $dbTables == $this::ALL_TABLES )
            $dbTables = $this->_db->getTables();

        foreach ( $dbTables as $tableName )
            $requiredTables[$tableName] = $this->loadTable( $tableName );

        return $requiredTables;
    }

    /**
     * Loads rows from the given tables where the associated ID is set and returns them
     * 
     * <pre>pattern of $dbTablesIDs:
     * [
     *      'exampleTbl1' => 1,
     *      'exampleTbl2' => 9
     * ]</pre>
     * 
     * @see LoadDatabase::requireRowsByCondition() If the ID-columns does not have the same name
     * @uses LoadDatabase::requireRowsByCondition()
     * @param array $dbTablesIDs Array of tables with the associated ID
     * @param string $idColumnsName The name of the ID-columns. Default is 'id'
     * @return array
     */
    public function requireRowsByID( array $dbTablesIDs, string $idColumnsName = 'id' ) {
        $dbTablesConditions = NULL;

        foreach ( $dbTablesIDs as $table => $id )
            $dbTablesConditions[$table] = [ $idColumnsName, $id ];
        
        return $this->requireRowsByCondition( $dbTablesConditions );
    }

    /**
     * Loads rows from the given tables where the associated condition is true and returns them
     * 
     * <pre>pattern of $dbTablesConditions:
     * [
     *      'exampleTbl1' => [ 'example1ID' => 1 ],                         # Single Condition
     *      'exampleTbl2' => [                                              # Multiple Conditions (default operator)
     *                  'example2ID' => 9,
     *                  'col2' => 'example'
     *      ],
     *      'exampleTbl3' => [                                              # Multiple Conditions with custom operator
     *                  '::ConditionOperator::' => 'OR',
     *                  't3ID'    => 9,
     *                  'default' => 1
     *      ],
     *      'exampleTbl4' => [ '::CustomCondition::' => 'name = \'test\'' ] # Custom Condition string
     * ]</pre>
     * 
     * @used-by LoadDatabase::requireRowsByID()
     * @uses LoadDatabase::_parseConditions() To parse the conditions array
     * @param array $dbTablesConditions Array of tables with the associated condition(s)
     * @return array
     */
    public function requireRowsByCondition( array $dbTablesConditions ) {
        $results = NULL;

        foreach ( $dbTablesConditions as $table => $conditions ) {
            $conditionStr = $this->_parseConditions( $conditions );
            $sql = $this->_db->query( $this->_query->select( $this->_db->quote( $table ), SQLQueryBuilder::SELECT_ALL_COLUMNS, false ).' '.
                                      $this->_query->where( $conditionStr ) );
            
            if ( $sql ) {
                $thisResults = NULL;
                while ( $result = $this->_db->getRecord( $sql ) ) {
                    if ( $result )
                        $thisResults[] = $result;
                    else
                        return false;
                }

                $results[$table] = $thisResults;
            }
        }

        if ( isset( $results ) )
            return $results;
        else
            return false;
    }

    /**
     * Parse the given condition array to a string
     * 
     * @param array $conditions Array of columns and associated values which could be interpreted as conditions
     * @return string
     */
    private function _parseConditions( array $conditions ) {
        $conditionOp = $this->_defaultOp;
        $conditionStr = '';

        if ( isset( $conditions['::CustomCondition::'] ) )
            $conditionStr = $conditions['::CustomCondition::'];
        else {
            if ( isset( $conditions['::ConditionOperator::'] ) ) {
                $conditionOp = $conditions['::ConditionOperator::'];
                unset( $conditions['::ConditionOperator::'] );
            }
            
            $lastCond = array_pop( $conditions );
            foreach ( $conditions as $col => $val)
                $conditionStr .= $this->_db->quote( $col ).'='.$this->_db->quote( $val ).' '.$conditionOp.' ';
            
            $conditionStr .= $this->_db->quote( key( $lastCond ) ).'='.$this->_db->quote( current( $lastCond ) );
        }

        return $conditionStr;
    }

    /**
     * Loads the given table from database and returns the results
     * 
     * @param string $tableName The name of the table to load
     * @return array
     */
    private function loadTable( string $tableName ) {
        $sql = $this->_db->query( $this->_query->select( $this->_db->quote( $tableName ), SQLQueryBuilder::SELECT_ALL_COLUMNS ) );
        if ( $sql ) {
            $thisResults = NULL;
            
            while ( $result = $this->_db->getRecord( $sql ) ) {
                if ( $result ) 
                    $thisResults[] = $result;
                else
                    return false;
            }

            if ( isset( $thisResults ) )
                return $thisResults;
            else
                return false;
        } else
            return false;
    }
}
?>
