<?php

/**
 * @file
 * Common functions shared by components.
 */

/**
 * Main function to execute the various deploy tasks.
 *
 * To Do: make dynamic for hooking.
 *
 * @param $cnf
 *   Array of confiiguration options.
 */
function deploy($cnf = array(), $post) {
  chdir(DEPLOY_DOCROOT);
  if ($cnf['debug']) {
    _deploy_log('Deploy Options: ' . serialize($cnf), 'debug', FALSE);
  }

  // Check for access key, script will if it fails.
  _deploy_access($_REQUEST['key'], $cnf['access_key']);

  // Run the requirements check.
  if ($cnf['requirements']) {
    _deploy_requirements($cnf);
  }

  // Check the payload and branch.
  $payload = _gitpull_deploy_payload_decode($post['payload'], $config['debug']);
  $branch = _gitpull_deploy_payload_branch($payload);

  // Branch matching
  if (!$branch == $cnf['git_branch']) {
    _deploy_log('Service Ping: ' . $branch . ' branch.', 'debug', FALSE);
    return FALSE;
  }

  // Deploy steps
  gitpull_deploy($cnf);
}

/**
 * Logging function.
 *
 * Errors are logged to the defined DEPLOY_DOCROOT. If something is very wrong
 * a PHP error is logged as well.
 *
 * To Do: Allow $log_message to include the timestamp so logging is accurate in
 * the gitpull_deploy() call.
 *
 * @param $log_message
 *   The log message string.
 * @param $type
 *   Basic notification warning levels. notice (default), error, crit, etc.
 * @param $print
 *   Whether or not to print the log message to the screen.
 */
function _deploy_log($log_message = null, $type = 'notice', $print = TRUE) {
  $log_entry = date('[Y-m-d H:i:sP]');
  $log_entry .= ' [' . $type . '] ' . $log_message . PHP_EOL;
  if ($print) {
    print nl2br($log_entry);
  }

  if (@file_put_contents(DEPLOY_LOG_FILE, $log_entry, FILE_APPEND) === FALSE) {
    $msg = '[git-deploy] Log file error using: ' . DEPLOY_LOG_FILE;
    trigger_error($msg, E_USER_ERROR);
    die($msg);
  }
}

/**
 * Check the submitted key agaist the access key.
 */
function _deploy_access($request_key, $access_key) {
  if ($request_key !== $access_key) {
    _deploy_log('Access denied from ['. $_SERVER['HTTP_REFERER'] .'] using key: '. $request_key, 'error', FALSE);
    die('Invalid!');
  } else {
    return TRUE;
  }
}

/**
 * Requirements check.
 *
 * Verify the commands are available and proper versions are met.
 *
 * To Do: Bust this up and clean it up.
 *
 * @param $cnf
 *   Array of confiiguration options.
 */
function _deploy_requirements($cnf = array()) {
  $error = 0; // error checks
  $prefix = '[git-pull_requirements] ';

  if (!is_dir(DEPLOY_DOCROOT)) { // Check for a specified docroot.
    _deploy_log($prefix . 'Docroot is not a valid directory!', 'crit');
    die($prefix . 'Error: Docroot, check log.');
  }

  // PHP checks
  $php_check = phpversion();
  $php_min_version = '5.2';

  if (!$php_check > $php_min_version) {
    _deploy_log($prefix . 'Error: PHP version is ' . $php_check . '!', 'crit');
    $error++;
  }

  // Git checks
  $git_check = exec($cnf['git_path'] .' --version', $git);
  $git_version_array = explode(' ', $git[0]);
  $git_min_version = '1.7';

  if (!$git_check) {
    _deploy_log($prefix . 'Error: Git command not found!', 'crit');
    $error++;
  }
  elseif ($git_version_array[2] < $git_min_version) {
    _deploy_log($prefix . 'Error: Git version is ' . $git_version_array[2] . '.', 'crit');
    $error++;
  }
  else {
    exec($cnf['git_path'] . ' status', $output); // Check the current status.
  }

  // Process the git $output
  foreach ($output AS $line) {
    $line = trim($line);
    if (!empty($line)) { // Is this a repo
      if (strpos($line, 'Not a git repository') !== FALSE) {
        _deploy_log($prefix . 'Error: The docroot does not contain a valid .git repository.', 'crit');
        $error++;
      }
      elseif (strpos($line, 'Untracked files') !== FALSE) {  // Any untracked files?
        _deploy_log($prefix . 'Error: The .git checkout containes untracked files.', 'crit');
        $error++;
      }
    }
  }

  // Drush checks.
  $prefix = '[drush_requirements] ';
  $drush_check = exec($cnf['drush_path'] . ' version 2>&1', $drush);
  $drush_version_array = explode(' ', $drush[0]);
  $drush_min_version = '4.0';

  if (!$drush_check) { // Is Drush accessible?
    _deploy_log($prefix . 'Error: Drush not found!', 'crit');
    $error++;
  }
  elseif ($drush_version_array[2] <= $drush_min_version) {  // Drush version check.
    _deploy_log($prefix . 'Error: Drush version is ' . $drush_version_array[2] . '.', 'crit');
    $error++;
  }

  if ($error) {
    die('[requirements] Requirement Errors! Check the deploy log.');
  }

  if ($cnf['debug']) {
    _deploy_log('[requirements] Passed Requirement Checks.');
  }

  return TRUE;
}

// Helper function for processing arrays.
function _deploy_walkarray($array) {
  // placeholder for later function
}

// Debug helper
function _deploy_debug($var, $name='') {
  if (!$name) $name = 'Deploy Debug';
  print'<pre>' . $name . ": ";
  print_r($var);
  print'</pre>';
}

// Debug helper alias
function dd($var, $name='') {
  _deploy_debug($var, $name);
}
