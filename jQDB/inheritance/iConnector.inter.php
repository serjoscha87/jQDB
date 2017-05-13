<?php

namespace jQDB;

/**
 * Note: the connecter shall always be able to guess data given as valid, secure and useable
 */
interface iConnector {
   
   public function getIdentifier();
   
   public function loadTableData($data);
   public function updateSingleField($data);
   public function updateRow($data);
   public function insertRow($data);
   
   public function authenticate($host, $username, $password, $db);
   
}
