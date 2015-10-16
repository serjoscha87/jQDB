<?php

namespace jQDB;

/*
 * A User Config needs to be a class extending this interface (leading the user to a working configuration)
 */
interface iConfig {
   
   public static function getUsername();
   public static function getPassword();
   public static function getHost();
   public static function useSecureMode();
   public static function getValidTables(); // this is needed when secure mode is enabled
   
}
