<?php

namespace jQDB;
use jQDB\ParameterObject AS PO;

class SecureModeHelper {
   
   const FAILURE_TABLE_INVALID = -1;
   const FAILURE_FIELD_INVALID = -2;
   const FAILURE_DB_INVALID = -3;

   /**
    * This method destroys the query causing it to crash, when secure mode is active but the table which is selected though JS is not configed as valid table.
    * This should prevent sql injections or the ability to view tables that shall not be viewed
    * @param String $tableName
    * @param ParameterObject $d The parameter...
    * @return String|singed int
    */
   public static function checkTable($tableName, $d) {
      if(Config::useSecureMode()) {
         if(in_array($d->getAttribute(PO::ATTR_DATABASE_NAME), Config::getValidDBs())){
            if(in_array($tableName, array_keys(Config::getValidTables()))) { // the desired table is configed as secure

               // gather *fields* that are used for building queries in any case (CRUD)
               $checkFields = array(
                   $d->getAttribute(PO::ATTR_CHANGED_FIELD),
               );
               if(is_array($d->getAttribute(PO::ATTR_PRIMARY_KEY_FIELDS)))
                  foreach ($d->getAttribute(PO::ATTR_PRIMARY_KEY_FIELDS) as $field) {
                     $checkFields[$field] = null;
                  }
               if(is_array($d->getAttribute(PO::ATTR_SELECTED_FIELDS)))
                  foreach (array_keys($d->getAttribute(PO::ATTR_SELECTED_FIELDS)) as $field) {
                     $checkFields[$field] = null;
                  }
               if(is_array($d->getAttribute(PO::ATTR_PRIMARY_KEY_DATA)))
                  foreach (array_keys($d->getAttribute(PO::ATTR_PRIMARY_KEY_DATA)) as $field) {
                     $checkFields[$field] = null;
                  }
               $insDataArray = array();
               parse_str($d->getAttribute(PO::ATTR_INSERT_DATA), $insDataArray);
               foreach (array_keys($insDataArray) as $field) {
                  $checkFields[$field] = null;
               }

               $checkFields = array_filter(array_keys($checkFields)); // remove null fields

               // now check each field if it is valid
               foreach ($checkFields as $field) {
                  if(!in_array($field, Config::getValidTables()[$d->getAttribute(PO::ATTR_TABLE)]))
                     return self::FAILURE_FIELD_INVALID;
               }

               return $tableName;
            }
            else
               return self::FAILURE_TABLE_INVALID;
         }
         else
            return self::FAILURE_DB_INVALID;
      }
      else
         return $tableName;
   }
   
   public static function isOrderValid($orderParam, $d) {
      if(Config::useSecureMode() && $orderParam) {
         $validOrders = array('asc', 'desc');
         $validFields = Config::getValidTables()[$d->getAttribute(PO::ATTR_TABLE)]; 
         if(in_array($orderParam[0], $validFields)){ // the selected field is valid
            if(in_array(strtolower($orderParam[1]), $validOrders)) // the direction is valid
               return true;
         }
         return false;
      }
      else
         return true;
   }

   /**
    * Method to check the fixed param keyword for validity and further more escape the free text elems (even if this is not semantic)
    * @param array $whereParam
    * @param ParameterObject $d 
    * @param bool $escapeFreeText if the function shall also escape the free text of the where clause
    * @return boolean
    */
   public static function isWhereClauseValid($whereParam, $d, $escapeFreeText=true) {
      if(Config::useSecureMode() && $whereParam) {
         //error_log(print_r($whereParam,true));
         $validFields = Config::getValidTables()[$d->getAttribute(PO::ATTR_TABLE)]; 
         $validOperators = array('LIKE', '=', '<=', '>=', '!=');
         $validConcaters = array('OR', 'AND');
         foreach ($whereParam as $param_group) {
            if(in_array($param_group[0], $validFields)) {
               if(in_array($param_group[1], $validOperators)){
                  if(in_array($param_group[3], $validConcaters) || $param_group[3]===null)
                     continue;
               }
            }
            return false;
         }
         if($escapeFreeText) { // secure free text elems 
            $whereParam = array_map ( function ($i) {
               $i[2] =  sprintf('"%s"',addslashes($i[2]));
               return $i;
            },$whereParam);
            $d->setAttribute(PO::ATTR_CONDITION, $whereParam);
         }
         return true;
      } // -- use Secure Mode off
      else
         return true;
   }
   
}
