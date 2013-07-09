<?php

/**
 * @file
 * The PHP page that serves all page requests for the deploy process.
 *
 */

/**
 * Defined constants needed throughout the scripts.
 */
define('DEPLOY_ROOT', getcwd());

require_once DEPLOY_ROOT .'/config.php';
require_once DEPLOY_ROOT .'/common.php';
require_once DEPLOY_ROOT .'/git-pull.php';

# Local overrides
if (file_exists(DEPLOY_ROOT .'/config.local.php')) {
  require(DEPLOY_ROOT .'/config.local.php');
}

define(GIT_PATH, $config['git_path']);

// Relative path to the checkout root.
define('DEPLOY_DOCROOT', '..');

// Path + name of the log file.
define('DEPLOY_LOG_FILE', DEPLOY_ROOT .'/deploy.log');


// Check the access key and if it matches run the deploy.
_deploy_access($_REQUEST['access_key'], $config['access_key']);

// Run the requirements check.
_deploy_requirements();

// Check the payload and branch.
$payload = _gitpull_deploy_payload_decode($_POST['payload'], $config['debug']);
$branch = _gitpull_deploy_payload_branch($payload);

// Deploy only if the branch matches $option['git_branch'].
if (!$branch == $config['git_branch']) {
    _deploy_log('Deploy Attempt: '. $branch, 'debug', FALSE);

} else {

  if ($config['debug']) {
    _deploy_log('Deploy Options: '. serialize($config), 'debug', FALSE);
  }

  // Run Deploy!
  deploy($config);
}
