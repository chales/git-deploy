<?php

/**
 * @file
 * Configuration options.
 */

/**
 * Configuration options passed into the deploy functions.
 *
 * Variables:
 * - access_key: this is the key that should be passed back from the
 *   hook url
 */
$config = array(
  'debug' => TRUE,
  'access_key' => 'mypassword',  // The access key to match.

  // Git options
  'git_path' => 'git', // Git.
  'git_branch' => 'develop', // Git branch that triggers a deploy.
  'git_remote' => 'origin', // Git remote.
  'git_clean' => TRUE, // Git clean.
  'git_reset' => TRUE, // Git reset hard.

  // Drush options (Can be refactored into it's own array for more commands)
  'drush_path' => '/usr/bin/drush', // drush binary.
  'drush_uri' => 'http://example.com', // Site uri for drush to target.
  'drush_fra' => FALSE, // Run features revert all.
);

// Set timezone for logging.
date_default_timezone_set('America/New_York');
