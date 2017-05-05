<?php

namespace jQDB;

/**
 * This class only maps Parameters that may be configured in jQDB to use with the IDE's autcomplete.
 * This class COULD without problems be completely omitted and is only for convenience reasons. Without it you would need to know all config key-strings
 * ~HELPER CLASS
 */
class ParameterObject {
   
   /**
    * local data-store
    * @var type 
    */
   private $data;
   
   /**
    * "enums"
    */
   const ATTR_TABLE = 'table';
   const ATTR_DATABASE_NAME = 'db';
   const ATTR_PRIMARY_KEY_FIELDS = 'primary_key_fields';
   const ATTR_SELECTED_FIELDS = 'fields';
   const ATTR_ELEMENTS_PER_PAGE = 'elements_per_page';
   const ATTR_CONNECTOR = 'connector';
   const ATTR_ORDER_BY = 'order_by';
   const ATTR_CONDITION = 'condition';
   const ATTR_PAGE = 'page';
   const ATTR_CODEBASE = 'codebase';
   
   // UPDATE SPECIFIC
   const ATTR_CHANGED_FIELD = 'changed_field';
   const ATTR_PRIMARY_KEY_DATA = 'pk_data';
   const ATTR_CHANGED_FIELD_NEW_VALUE = 'changed_nu_val';
   
   // INSERT SPECIFIC
   const ATTR_INSERT_DATA = 'insert_data';
   
   /*
    * Not really a parse method at all, but semantically it shall be the most conclusive name
    */
   public function parse($data) {
      $this->data = $data;
   }
   
   /**
    * 
    * @param String $attrib a attribute chosen from the const list within this class
    * @param mixed $returnOnNull anything that shall be returned by this function when the desired value to get is null (for example "array()" so loops won't cause a crash)
    * @return type
    */
   public function getAttribute($attrib, $returnOnNull=null) {
      return isset($this->data[$attrib])?$this->data[$attrib]:$returnOnNull;
   }
   
   public function __construct() {
      if(func_num_args() === 1)
         $this->parse (func_get_arg (0));
   }
   
   public function setAttribute($attrib, $nu_val) {
      $this->data[$attrib] = $nu_val;
   }
   
   /**
    * this method may return modified data (according to security rules)
    */
   public function getData() {
      return $this->data;
   }
   
}
