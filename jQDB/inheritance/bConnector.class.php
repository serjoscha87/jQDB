<?php

namespace jQDB;

/**
 * Connector base / commons
 */
class bConnector {
   
   /*
    * map generic ajax incoming commands to connector methods
    */
   private $calls = array(
       'load' => 'loadTableData',
       'update' => 'updateSingleField',
       'insert' => 'insertRow',
       'delete' => 'deleteRow'
   );
   
   public function __call($name, $arguments) {
      $method_2_call = $this->calls[$name];
      return $this->$method_2_call($arguments[0]); // as soon there is a method within the list of reflection method that takes 2 parameters -> remove [0]
   }
   
   /**
    * Easy one-line query clause string building method
    * @param array $data key,value pairs to be combined to a condition
    * @return String the String that can be used after WHERE
    */
   protected  function easyBuildQueyClause($data, $concater='AND') {
      return str_replace(',', $concater, substr(preg_replace('/"(\w*)":/' , '$1=' , json_encode($data)),1,-1)); // so obvious!
   }
   
}
