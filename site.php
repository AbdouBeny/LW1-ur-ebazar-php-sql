<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * point d'entrée 
 */
set_include_path(__DIR__ . "/src");

require_once("Router.php");
// Initialiser les storages
require_once("model/AnnonceStorageSession.php");
require_once("model/UserStorageSession.php");
require_once("model/CategoryStorageSession.php");
require_once("model/AchatStorageSession.php");

$annonceStorage = new AnnonceStorageSession();
$userStorage = new UserStorageSession();
$categoryStorage = new CategoryStorageSession();
$achatStorage = new AchatStorageSession();


$router = new Router();
$router->main($annonceStorage, $userStorage, $categoryStorage, $achatStorage);