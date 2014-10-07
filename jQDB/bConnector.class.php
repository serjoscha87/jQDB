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
   
}
