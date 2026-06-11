<?php
// Load environment variables FIRST
require_once 'config/env.php';

// Load error handler SECOND
require_once 'config/error-handler.php';

// Load configuration
require_once 'config/constants.php';
require_once 'config/auth.php';
require_once 'config/functions.php';

logout();
redirectTo('login');
