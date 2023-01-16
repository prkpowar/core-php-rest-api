<?php
require 'vendor/autoload.php';
use Dotenv\Dotenv;

const PROJECT_ROOT_PATH = __DIR__ . "/../";

$dotenv = new DotEnv(PROJECT_ROOT_PATH);
$dotenv->load();

define('DB_HOST', getenv('DB_HOST'));
define('DB_USERNAME', getenv('DB_USERNAME'));
define('DB_PASSWORD',getenv('DB_PASSWORD'));
define('DB_DATABASE', getenv('DB_DATABASE'));