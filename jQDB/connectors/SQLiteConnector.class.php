<?php

namespace jQDB;
use jQDB\ParameterObject AS PO;

class SQLiteConnector extends bConnector implements iConnector {
   
   /**
    * @var PDO|null
    */
   private $dbh = null;
   
   /**
    * Tell the type / name of this connector
    * @return string
    */
   public function getIdentifier() {
      return 'sqlite';
   }
   
   public function authenticate($host=null, $username=null, $password=null, $dbfile_path) {
      try { // when auth successes
         $this->dbh = new \SQLite3($dbfile_path);
         return array('success' => true);
      }catch(\Exception $e) {
         // when auth fails
         return array('success' => false, 'error_str' => 'could not open sqlite db');
      }
   }
   
   /**
    * 
    * @param array $data
    * @return array serializeable aray
    */
   public function loadTableData($data) {
      $d = new ParameterObject($data);
      
      $fields = implode(', ', array_keys($d->getAttribute(PO::ATTR_SELECTED_FIELDS))); // fields to show
      
      $where_condition = 'WHERE ';
      foreach ($d->getAttribute(PO::ATTR_CONDITION,array()) as $condition_row) {
         $where_condition .= sprintf(' %s ',implode(' ', $condition_row));
      }
      
      /*$fieldsQuery = $this->dbh->query("PRAGMA table_info(".$d->getAttribute(PO::ATTR_TABLE).")");
      $real_table_fields = []; // concrete and existing db fields
      while($iter_data = $fieldsQuery->fetchArray(SQLITE3_ASSOC))
         $real_table_fields[] = $iter_data['name'];
      
      $selected_fields = array_keys($d->getAttribute(PO::ATTR_SELECTED_FIELDS)); // from js config
      
      if(count(array_intersect($selected_fields,$real_table_fields)) < count($selected_fields)){
         return array(
             'success' => false, 
             'error_str' => sprintf('You tried to select fields (%s) that are not existing!', implode(',', array_diff($selected_fields,$real_table_fields)))
         );
      }*/
      
      $resultsQuery = @$this->dbh->query(sprintf('SELECT %s, %s FROM %s %s %s %s', 
              implode(',', $d->getAttribute(PO::ATTR_PRIMARY_KEY_FIELDS)),
              $fields,
              $d->getAttribute(PO::ATTR_TABLE),
              $d->getAttribute(PO::ATTR_CONDITION) ? $where_condition : '',
              $d->getAttribute(PO::ATTR_ORDER_BY) ? sprintf('ORDER BY %s %s', $d->getAttribute(PO::ATTR_ORDER_BY)[0], $d->getAttribute(PO::ATTR_ORDER_BY)[1]) : '', // TODO enable multiple sort possibility
              $d->getAttribute(PO::ATTR_ELEMENTS_PER_PAGE) ? sprintf('LIMIT %d,%d', 
              $d->getAttribute(PO::ATTR_PAGE)*$d->getAttribute(PO::ATTR_ELEMENTS_PER_PAGE), 
              $d->getAttribute(PO::ATTR_ELEMENTS_PER_PAGE)) : ''
      ));
              
      /*
       * proceed or catch mistaken queries
       */
      if($resultsQuery) { 
         $resAll = [];
         while($iter_data = $resultsQuery->fetchArray(SQLITE3_ASSOC))
            $resAll[] = $iter_data;
         return array( // everything okay
             'results' => $resAll,
             // get over all result amount
             'result_amount' => $this->dbh->querySingle(sprintf('SELECT COUNT(*) FROM %s %s',
                 $d->getAttribute(PO::ATTR_TABLE),
                 $d->getAttribute(PO::ATTR_CONDITION) ? $where_condition : ''
             )),
             'success' => true
            );
      }
      else
         return array( // something went wrong
             'success' => false, 
             'error_str' => sprintf('Failure due fetching the table Data: %s',$this->dbh->lastErrorMsg())
         );
   }
   
   public function updateSingleField($data) {

      $d = new ParameterObject($data);
      
      $qry = sprintf('UPDATE "%s" SET "%s"=\'%s\' WHERE %s',
               $d->getAttribute(PO::ATTR_TABLE),
               $d->getAttribute(PO::ATTR_CHANGED_FIELD),
               $d->getAttribute(PO::ATTR_CHANGED_FIELD_NEW_VALUE),
               $this->easyBuildQueyClause($d->getAttribute(PO::ATTR_PRIMARY_KEY_DATA)));
      
      return array('success' => $this->dbh->exec($qry));
   }
   
   public function insertRow($data) {
      $d = new ParameterObject($data);

      $qry = sprintf('INSERT INTO %s ("%s") VALUES("%s") ', 
              $d->getAttribute(PO::ATTR_TABLE), 
              implode($is='", "', array_keys($ins_dat = $d->getAttribute(PO::ATTR_INSERT_DATA))),
              implode($is, $ins_dat)
      );
      
      return array(
          'success' => $this->dbh->exec($qry),
          'id' => $this->dbh->lastInsertRowid()
      );
   }
   
   public function deleteRow($data) {
      $d = new ParameterObject($data);
      
      $qry = sprintf('DELETE FROM %s WHERE %s', 
              $d->getAttribute(PO::ATTR_TABLE), 
              $this->easyBuildQueyClause($d->getAttribute(PO::ATTR_PRIMARY_KEY_DATA)) 
      );
      
      $affected = $this->dbh->exec($qry);
      return array('success' => $affected);
   }
   
   /**
    * Easy one-line query clause string building method
    * @param array $data key,value pairs to be combined to a condition
    * @return String the String that can be used after WHERE
    */
   private function easyBuildQueyClause($data, $concater='AND') {
      return str_replace(',', $concater, substr(preg_replace('/"(\w*)":/' , '$1=' , json_encode($data)),1,-1)); // so obvious!
   }
   
}