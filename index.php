<?php

/**
 * @file
 * The PHP page that serves all page requests for the deploy process.
 *
 */

/**
 * Defined constants and include files needed throughout the scripts.
 */
define('DEPLOY_ROOT', getcwd());

require_once DEPLOY_ROOT .'/config.php';
require_once DEPLOY_ROOT .'/common.php';
require_once DEPLOY_ROOT .'/git-pull.php';

# Local overrides
if (file_exists(DEPLOY_ROOT .'/config.local.php')) {
  require(DEPLOY_ROOT .'/config.local.php');
}

// Relative path to the checkout root.
define('DEPLOY_DOCROOT', '.');

// Path + name of the log file.
define('DEPLOY_LOG_FILE', DEPLOY_ROOT .'/deploy.log');

// Run Deploy!
deploy($config, $_POST);
