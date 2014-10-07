<?php

namespace jQDB;

class ConnectorSelector {
   
   public $connectors = null;

   public function __construct() {
      // built connector list
      foreach (glob('connectors/*.class.php') as $connector_file) {
         require_once $connector_file;
         /* @var $connector iConnector */
         $connector_cn = array_pop(get_declared_classes()); // get the connectors class name
         $connector_obj = new $connector_cn;
         $this->connectors[$connector_obj->getIdentifier()] = $connector_obj;
      }
   }
   
   /**
    * 
    * @param String $connector
    * @return iConnector the concrete connector class object
    */
   public function getConector($connector) {
      return $this->connectors[$connector];
   }
   
}
