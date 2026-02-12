<?php
/**
 * FluxBB Configuration Template
 *
 * Copy this file to your FluxBB installation directory as config.php
 * and update the values to match your environment.
 */

// Database configuration
$db_type = 'mysqli';
$db_host = getenv('DB_HOST') ?: 'mariadb';
$db_name = getenv('DB_NAME') ?: 'fluxbb';
$db_username = getenv('DB_USER') ?: 'fluxbb';
$db_password = getenv('DB_PASSWORD') ?: 'fluxbb';
$db_prefix = getenv('DB_PREFIX') ?: 'fluxbb_';
$p_connect = false;

// Cookie configuration
$cookie_name = 'fluxbb_cookie';
$cookie_domain = '';
$cookie_path = '/';
$cookie_secure = 0;
$cookie_seed = 'change_this_to_random_string';

// Base URL (no trailing slash)
$base_url = 'http://localhost:8080/archive';
