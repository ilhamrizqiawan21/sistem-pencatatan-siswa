<?php
require_once __DIR__ . '/env.php';

// Read from environment with sensible defaults
define('BASE_URL', getenv('BASE_URL') ?: 'https://ilham.didzacorp.com/mts-alihsan/');

// Allow UPLOAD_PATH to use placeholder __DOCUMENT_ROOT__ in .env
$upload_path_env = getenv('UPLOAD_PATH') ?: ($_SERVER['DOCUMENT_ROOT'] . '/mts-alihsan/uploads/');
if (strpos($upload_path_env, '__DOCUMENT_ROOT__') !== false) {
	$upload_path_env = str_replace('__DOCUMENT_ROOT__', rtrim($_SERVER['DOCUMENT_ROOT'], '/'), $upload_path_env);
}
define('UPLOAD_PATH', $upload_path_env);
define('UPLOAD_URL', getenv('UPLOAD_URL') ?: BASE_URL . 'uploads/');
?>