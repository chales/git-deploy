<?php

/**
 * Example configuration showing some possible overrides for your local system.
 */

// Git options
$config['git_path'] = '/usr/local/git/bin/git'; // Git.
$config['git_clean'] = FALSE; // Git clean.
$config['git_reset'] = FALSE; // Git reset hard.

/**
 * Payload testing JSON snip for Bitbucket.
 */

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
