<?php
/**
 * File: loadDatabase.class.php
 * Project: libs
 * File Created: Wednesday, 31st January 2018 9:54:48 am
 * Author: ramon1611
 * -----
 * Last Modified: Wednesday, 31st January 2018 10:07:53 am
 * Modified By: ramon1611
 */

namespace ramon1611\Libs\Ticket;

class LoadDatabase {
    const ALL_TABLES = [ '*' ];

    private $_db = NULL;

    public function __construct( $dbObject = NULL ) {
        if ( isset( $dbObject ) )
            $this->_db = $dbObject;
        elseif ( isset( $GLOBALS['db'] ) )
            $this->_db = $GLOBALS['db'];
        else
            trigger_error( 'No Database Object provided!', E_USER_ERROR );
    }

    public function require( array $dbTables ) {
        $requiredTables = NULL;

        if ( $dbTables == ALL_TABLES )
            $dbTables = array_keys( $GLOBALS['tables'] );

        foreach ( $dbTables as $tableName )
            if ( method_exists( $this, 'load_'.$tableName ) )
                $return[$tableName] = call_user_func( [ $this, 'load_'.$tableName ] );

        return $requiredTables;
    }

    private function load_labels() {
        $sql = $GLOBALS['db']->query( $GLOBALS['query']->select( $GLOBALS['db']->quote( $GLOBALS['tables']['labels'] ), $GLOBALS['query']::SELECT_ALL_COLUMNS ) );
        if ( $sql ) {
            $thisLabels = NULL;
            
            while ( $result = $GLOBALS['db']->getRecord( $sql ) ) {
                if ( $result ) {
                    $id = $result[$GLOBALS['columns']['labels']['ID']];
                    $bgColor = ( isset( $result[$GLOBALS['columns']['labels']['bg-color']] ) ) ?
                                    $result[$GLOBALS['columns']['labels']['bg-color']] :
                                    $GLOBALS['settings']['label.default.bg-color'];
                    $textColor = ( isset( $result[$GLOBALS['columns']['labels']['text-color']] ) ) ?
                                    $result[$GLOBALS['columns']['labels']['text-color']] :
                                    $GLOBALS['settings']['label.default.text-color'];

                    $thisLabels[$id] = array(
                        'name'          => $result[$GLOBALS['columns']['labels']['name']],
                        'displayName'   => $result[$GLOBALS['columns']['labels']['displayName']],
                        'bg-color'      => $bgColor,
                        'text-color'    => $textColor
                    );
                } else
                    return false;
            }

            if ( isset( $thisLabels ) )
                return $thisLabels;
            else
                return false;
        } else
            return false;
    }

    private function load_userHandlers() {
        $sql = $GLOBALS['db']->query( $GLOBALS['query']->select( $GLOBALS['db']->quote( $GLOBALS['tables']['userHandlers'] ), $GLOBALS['query']::SELECT_ALL_COLUMNS ) );
        if ( $sql ) {
            while ( $result = $GLOBALS['db']->getRecord( $sql ) ) {
                if ( $result ) {
                    $GLOBALS['userHandlers'][$result[$GLOBALS['columns']['userHandlers']['name']]] = array(
                        'ID'      => $result[$GLOBALS['columns']['userHandlers']['ID']],
                        'pageID'    => $result[$GLOBALS['columns']['userHandlers']['pageID']]
                    );
                } else
                    return false;
            }
        } else
            return false;
    }
}
?>
