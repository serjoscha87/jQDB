<?php

namespace jQDB;

/*
 * Note: table and db are not configed here, so we could ease create multiple table instances on different tables/dbs
 */
class Config implements iConfig {
   
   public static function getHost() {
      return "localhost";
   }
   
   public static function getPassword() {
      return "";
   }
   
   public static function getUsername() {
      return "root";
   }
   
   /**
    * NOTE: SecureMode currently affects tables, not cols (etc) !
    */
   public static function useSecureMode() {
      return true;
   }
   
   /*
    * ONLY NEEDED WHEN useSecureMode RETURNS TRUE (and so secure mode is enabled)
    */
   public static function getValidTables() {
      return array(
          'test_1' => array(
              // valid fields (all that are used for any query must be defined, otherwise secure mode blocks using a used field)
              'id',
              'foo',
              'bar',
              'some_bool'
              ),
          );
      //return array();
   }
   
   public static function getValidDBs() {
      return array('test');
   }
   
}
