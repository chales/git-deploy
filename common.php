<?php

/**
 * @file
 * Common functions shared by components.
 */

/**
 * Main function to execute the various deploy tasks.
 *
 * To Do: make dynamic for hooking.
 */

function deploy($cnf = array()) {
  gitpull_deploy($cnf);
}

/**
 * Logging fucntion.
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
 *   Whether to print the log message to the screen.
 */
function _deploy_log($log_message = null, $type = 'notice', $print = TRUE) {
  /*
   * Accept timestamp as part of the log entry.
  if (is_array($log_message)) {
    date('[Y-m-d H:i:sP]') = key($log_entry);
  } else {
    $log_entry = date('[Y-m-d H:i:sP]');
  }
  */
  $log_entry = date('[Y-m-d H:i:sP]');
  $log_entry .= ' ['. $type .'] '. $log_message . PHP_EOL;
  if ($print) {
    print nl2br($log_entry);
  }

  if (@file_put_contents(DEPLOY_LOG_FILE, $log_entry, FILE_APPEND) === FALSE) {
    $msg = '[git-deploy] Log file error using: '. DEPLOY_LOG_FILE;
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
 * To Do: Bust this up and clean it up.
 *
 * Verify the commands are available and proper versions are met.
 */
function _deploy_requirements() {
  $error = 0; // error checks
  $prefix = '[git_update_requirements_check] ';

  if (!is_dir(DEPLOY_DOCROOT)) { // Check for a specified docroot.
    _deploy_log($prefix .'Docroot is not a valid directory!', 'crit');
    die($prefix .'Error: Docroot, check log.');
  }

  chdir(DEPLOY_DOCROOT);
  // PHP checks
  $php_check = phpversion();
  $php_min_version = '5.2';

  if (!$php_check > $php_min_version) {
    _deploy_log($prefix .'Error: PHP version is '. $php_check .'!', 'crit');
    $error++;
  }

  // Git checks
  $git_check = exec(GIT_PATH .' --version', $git);
  $git_version_array = explode(' ', $git[0]);
  $git_min_version = '1.7';

  if (!$git_check) {
    _deploy_log($prefix .'Error: Git command not found!', 'crit');
    $error++;
  }
  elseif ($git_version_array[2] < $git_min_version) {
    _deploy_log($prefix .'Error: Git version is '. $git_version_array[2] .'.', 'crit');
    $error++;
  }
  else {
    exec('git status', $output); // Log current status.
  }

  // Process the git $output
  foreach($output AS $line) {
    $line = trim($line);
    if (!empty($line)) { // Is this a repo
      if (strpos($line, 'Not a git repository') !== FALSE) {
        _deploy_log($prefix .'Error: The docroot does not contain a valid .git repository.', 'crit');
        $error++;
      }
      elseif (strpos($line, 'Untracked files') !== FALSE) {  // Any untracked files?
        _deploy_log($prefix .'Error: The .git checkout containes untracked files.', 'crit');
        $error++;
      }
      _deploy_log($line);
    }
  }

  // Drush checks.
  $drush_check = exec(DRUSH_PATH .' version', $drush);
  $drush_version_array = explode(' ', $drush[0]);
  $drush_min_version = '4.0';

  if (!$drush_check) { // Is Drush accessible?
    _deploy_log($prefix .'Error: Drush not found!', 'crit');
    $error++;
  }
  elseif ($drush_version_array[2] <= $drush_min_version) {  // Drush version check.
    _deploy_log($prefix .'Error: Drush version is '. $drush_version_array[2] .'.', 'crit');
    $error++;
  }

  if ($error) {
    die($prefix .'Requirement Errors! Check the deploy log.');
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
  print'<pre>';
  print $name .":\n";
  print_r($var);
  print'</pre>';
}

// Debug helper alias
function dd($var, $name='') {
  _deploy_debug($var, $name);
}
