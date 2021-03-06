<?php
/*
 * Purpose of this file:
 * This is the most central part which the js-part talks to and which puts everything together (config+helper) to get useable responses.
 * This connecter should be able to deal with any connector that is configed (so its name is generic...)
 */

namespace jQDB;
use jQDB\ParameterObject AS PO;

//error_reporting(0); // for this script should always only return json -> warnings and errors would destroy everything

// inheritance
require_once 'inheritance/iConnector.inter.php';
require_once 'inheritance/bConnector.class.php';
require_once 'inheritance/iConfig.inter.php';

// helper
require_once 'helper/ConnectorSelector.class.php';
require_once 'helper/ParameterObject.class.php';
require_once 'helper/SecureModeHelper.class.php';

$dObj = new ParameterObject($_POST['options']);

require_once 'config.class.inc.php'; // the point where the user config is loaded (removed dynamic path configuration from js for security reasons)

$desired_connector = $dObj->getAttribute(PO::ATTR_CONNECTOR);

$cs = new ConnectorSelector();
$connector = $cs->getConector($desired_connector); // try to select the connector

// host, username, password, db
$authRes = $connector->authenticate(
   Config::getHost(), // this may be unnecessary in some cases (sqlite for example)
   Config::getUsername(),
   Config::getPassword(),
   $dObj->getAttribute(PO::ATTR_DATABASE_NAME)
);

$checkRes = SecureModeHelper::checkTable($dObj->getAttribute(PO::ATTR_TABLE), $dObj);
if(is_numeric($checkRes)) { // we are returned a string in valid case or an int in failure case
   $data = array('success' => false);
   if($checkRes === SecureModeHelper::FAILURE_TABLE_INVALID)
      $data['error_str'] = 'You tried to us a table which is not on the whitelist due Secure Mode. Disable Secure Mode or add '.$dObj->getAttribute(PO::ATTR_TABLE).' to the list of allowed tables';
   elseif($checkRes === SecureModeHelper::FAILURE_FIELD_INVALID)
      $data['error_str'] = 'You tried to us a table field which is not on the whitelist due Secure Mode. Disable Secure Mode or add all desired fields to the list of allowed fields';
   elseif($checkRes === SecureModeHelper::FAILURE_DB_INVALID)
      $data['error_str'] = 'You tried to select a DB which is not on the whitelist due Secure Mode. Disable Secure Mode or add '.$dObj->getAttribute(PO::ATTR_DATABASE_NAME).' to the list of allowed DBs';
}
elseif(!is_numeric($dObj->getAttribute(PO::ATTR_ELEMENTS_PER_PAGE))) {
   $data = array('success' => false, 'error_str' => 'You configured Elements-PerPage failed the Security rules');
}
elseif(!is_numeric($dObj->getAttribute(PO::ATTR_PAGE)) && !is_null($dObj->getAttribute(PO::ATTR_PAGE))) {
   $data = array('success' => false, 'error_str' => 'You desired page failed the Security rules');
}
elseif(!SecureModeHelper::isOrderValid($dObj->getAttribute(PO::ATTR_ORDER_BY),$dObj)) {
   $data = array('success' => false, 'error_str' => 'You configured Order-By Clause does not match the configed Secure Mode settings');
}
elseif(!SecureModeHelper::isWhereClauseValid($dObj->getAttribute(PO::ATTR_CONDITION),$dObj)) {
   $data = array('success' => false, 'error_str' => 'You configured Where Clause does not match the configed Secure Mode settings');
}
elseif($authRes['success']) { // only call desired ajax function when the authentication was successfully
   $action = $_POST['action']; // using reflection to determine the function to use -> the functions are mapped by the class bConnector
   //$data = $connector->$action($_POST['data']); // method to be called on the chosen selector gets mapped and reflecteed through bConnector.class.php
   $data = $connector->$action($dObj->getData()); // method to be called on the chosen selector gets mapped and reflecteed through bConnector.class.php
}
else // otherwise return the auth-methods return as json
   $data = $authRes;

echo json_encode($data);

/*
 * Footnote: Okay okay, this ain't a actory (not even a class) but somehow it builds things up 
 */