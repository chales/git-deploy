<?php

/**
 * @file
 * Configuration options.
 */

/**
 * Make a duplicate of this file and name it config.php for overrides to apply.
 *
 * You can override the whole array or individual elements. e.g.
 * $config['git_path'] = '/usr/local/git/bin/git';
 *
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
  'git_path' => '/usr/bin/git', // Git.
  'git_branch' => 'develop', // Git branch.
  'git_remote' => 'origin', // Git remote.
  'git_clean' => FALSE, // Git clean - remove unrtacked files.
  'git_reset' => FALSE, // Git reset hard - reset modified files.

  // Drush options
  'drush_path' => '/usr/bin/drush', // drush binary.
  'drush_uri' => 'http://example.com', // Site uri for drush to target.
  'drush_fra' => FALSE, // Run features revert all.

  // During setup leave TRUE to confirm and log the requirements.
  'requirements' => TRUE,
);

// Set timezone for logging.
date_default_timezone_set('America/New_York');

/**
 * Example payload testing JSON snip for Bitbucket.
 * Uncoment to use this snippet.
 */
/*
$_POST['payload'] = '{
  "canon_url": "https://bitbucket.org",
  "commits": [
    {
      "branch": "develop",
      "utctimestamp": "2012-05-30 03:58:56+00:00"
    }
  ],
  "user": "jdoe"
}';
*/
