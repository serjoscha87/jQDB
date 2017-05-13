<?php

namespace jQDB;
use jQDB\ParameterObject AS PO;

class MySQLConnector extends bConnector implements iConnector {
   
   /**
    * @var PDO|null
    */
   private $dbh = null;
   
   /**
    * Tell the type / name of this connector
    * @return string
    */
   public function getIdentifier() {
      return 'mysql';
   }
   
   public function authenticate($host, $username, $password, $db) {
      try { // when auth successes
         $this->dbh = new \PDO(sprintf('mysql:host=%s;dbname=%s', $host, $db), $username, $password);
         $this->dbh->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
         return array('success' => true);
      }catch(\PDOException $e) {
         // when auth fails
         return array('success' => false, 'error_str' => 'database connection failed due wrong server-authentication data');
      }
   }
   
   /**
    * 
    * @param array $data
    * @return array serializeable aray
    */
   public function loadTableData($data) {
      $d = new ParameterObject($data);
      
      /*$filtered_selected_fields = array_filter($d->getAttribute(PO::ATTR_SELECTED_FIELDS), function ($obj) { // throw out custom fields (because those are considere not to be real db fields)
         return $obj['type']!=='custom';
      });*/
      
      $fields = implode(', ', array_keys($d->getAttribute(PO::ATTR_SELECTED_FIELDS))); // fields to show
      
      $where_condition = 'WHERE ';
      foreach ($d->getAttribute(PO::ATTR_CONDITION,array()) as $condition_row) {
         $where_condition .= sprintf(' %s ',implode(' ', $condition_row));
      }
      
      $resultsQuery = $this->dbh->query(sprintf('SELECT %s, %s FROM %s %s %s %s', 
         implode(',', $d->getAttribute(PO::ATTR_PRIMARY_KEY_FIELDS)),
         $fields,
         $d->getAttribute(PO::ATTR_TABLE),
         $d->getAttribute(PO::ATTR_CONDITION) ? $where_condition : '',
         $d->getAttribute(PO::ATTR_ORDER_BY) ? sprintf('ORDER BY %s %s', $d->getAttribute(PO::ATTR_ORDER_BY)[0], $d->getAttribute(PO::ATTR_ORDER_BY)[1]) : '', // TODO enable multiple sort possibility
         $d->getAttribute(PO::ATTR_ELEMENTS_PER_PAGE) ? sprintf('LIMIT %d,%d', 
         $d->getAttribute(PO::ATTR_PAGE)*$d->getAttribute(PO::ATTR_ELEMENTS_PER_PAGE), 
         $d->getAttribute(PO::ATTR_ELEMENTS_PER_PAGE)) : ''
      ));
      
      if($resultsQuery) { // catch mistaken queries
         return array( // everything okay
            'results' => $resultsQuery->fetchAll(),
            // get over all result amount
            'result_amount' => $this->dbh->query(sprintf('SELECT null FROM %s %s',
               $d->getAttribute(PO::ATTR_TABLE),
               $d->getAttribute(PO::ATTR_CONDITION) ? $where_condition : ''
            ))->rowCount(),
            'success' => true
         );
      }
      else
         return array( // something went wrong
            'success' => false, 
            'error_str' => sprintf('Failure due fetching the table Data: %s',$this->dbh->errorInfo()[2])
         );
   }
   
   public function updateSingleField($data) {

      $d = new ParameterObject($data);
      
      $qry = sprintf('UPDATE %s SET `%s`="%s" WHERE %s',
         $d->getAttribute(PO::ATTR_TABLE),
         $d->getAttribute(PO::ATTR_CHANGED_FIELD),
         $d->getAttribute(PO::ATTR_CHANGED_FIELD_NEW_VALUE),
         $this->easyBuildQueyClause($d->getAttribute(PO::ATTR_PRIMARY_KEY_DATA))
      );

      return array(
         'success' => $this->dbh->exec($qry),
         'id' => implode($d->getAttribute(PO::ATTR_PRIMARY_KEY_DATA))
      );
   }
   
   public function insertRow($data) {
      $d = new ParameterObject($data);

      $fieldSetterString = $this->easyBuildQueyClause($d->getAttribute(PO::ATTR_INSERT_DATA),',');
       
      $qry = sprintf('INSERT INTO %s SET %s', $d->getAttribute(PO::ATTR_TABLE), $fieldSetterString);

      return array(
         'success' => $this->dbh->exec($qry),
         'id' => $this->dbh->lastInsertId() 
      );
   }
   
   public function deleteRow($data) {
      $d = new ParameterObject($data);
      
      $qry = sprintf('DELETE FROM %s WHERE %s', $d->getAttribute(PO::ATTR_TABLE), $this->easyBuildQueyClause($d->getAttribute(PO::ATTR_PRIMARY_KEY_DATA)) );
      $affected = $this->dbh->exec($qry);
      return array('success' => $affected);
   }
   
}