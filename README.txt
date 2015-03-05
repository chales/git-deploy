Script Details
Basics
The main objective was to use a Git service hook to trigger a deployment (update) whenever a commit is made against the branch your target site is using. i.e. if your staging site is running the develop branch then commits made to develop trigger an update.

I wanted to keep things fairly agnostic and keep it in simple procedural PHP so anyone with Drupal familiarity could easily read and follow along with the code. I've set things up with just a few options and sane defaults but have done a major rewrite on what started as a single file for it to be more flexible.

For Drupal it could be more integrated with Drush and have a module component to integrate the logging and status details into watchdog and the status page.

The scripts are currently publicly available on Github.
https://github.com/chales/git-deploy

Security
A variable for “key” is used in requests as a basic security option and passed as part of the URL. This is to keep and outside source from triggering the deploy.

e.g. https://www.example.com/mis-deploy/index.php?key=64e2cfcde12d91dfc3496b7cd71

Renaming the directory itself to something unique is also good for obscurity,
e.g. https://www.example.com/8f1b0fb0a9f/index.php?key=64e2cfcde12d91dfc3496b7cd71
Requirements
I’ve added a requirements check function to test for git, git version, drush, docroot, logging, php version, and a fer other sanity check items.

This will be fleshed out more as we add more features, each with it’s own set.

Configuration Options
I’ve added some constants for the main set and forget parts of the script and an array for options to be passed into the various deploy functions.

define('DEPLOY_DOCROOT', '..'); // Path to the checkout root.
define('DEPLOY_LOG_FILE', DEPLOY_DOCROOT .'./deploy.log'); // Path + filename of the log file

$config = array(
  'debug' => TRUE,
  'access_key' => 'abc123',  // The access key to match.

  // Git options
  'git_path' => '/usr/local/bin/git', // Git.
  'git_branch' => 'develop', // Git branch that triggers a deploy.
  'git_remote' => 'origin', // Git remote.
  'git_clean' => TRUE, // Git clean.
  'git_reset' => TRUE, // Git reset hard.

  // Drush options
  'drush_path' => '/usr/bin/drush', // drush binary.
  'drush_uri' => 'http://example.com, // Site uri for drush to target.
  'drush_fra' => FALSE, // Run features revert all.

  // Leave TRUE to confirm and log the requirements.
  'requirements' => TRUE;
);


Service selection can be worked on a little more to automate it or this can be done via a settings include.
Overrides
It’s setup to check for a config.local.php file to include which is set to be ignored similar to how you would use settings.php. This however is for config overrides and an example file is included.
Logging
I went a little overboard on the logging so it’s very heavy in the current version so that bugs can be ironed out. The file will be created if not present and it’s set to be ignored in the default directory. Once it’s been tested in a variety of environments overall logging can be reduced or a log level introduced.

Service Integration

Github and Bitbucket produce a JSON “payload” POST request to the defined service hook URL. This data can be parsed to discover details about the update such as the branch. I believe we can easily setup a regex watch for specified keywords such as “release” to use with production but more testing is needed.

Github
Github calls their hook a Post-Receive Webhook, https://help.github.com/articles/post-receive-hooks

Add a service under the repo settings and choose Webhook URL. e.g. http://chales-demo.mediacurrentstaging.info/mis-deploy/index.php?access_key=abc123

A POST is sent with a payload of data about the commit in a JSON format. See the example on their testing page, https://help.github.com/articles/testing-webhooks
Bitbucket
For BItbucket repos visit the repo settings and select services. Choose the POST option and set a URL. e.g. http://chales-demo.mediacurrentstaging.info/mis-deploy/index.php?access_key=abc123

A POST is sent with a payload of data about the commit to the specified URL. More information can be found in the service setup details, https://confluence.atlassian.com/display/BITBUCKET/Managing+Bitbucket+Services

More info is available on the specifics of the POST service, https://confluence.atlassian.com/display/BITBUCKET/POST+Service+Management
Basic Auth Access
Basic auth password access can be added to the URI for staging sites under lockdown. e.g.
http://chales:MyPass@example.com/mis-deploy/index.php?access_key=abc123

Server Requirements
SSH Keys
The Apache user will need a passwordless ssh key. This will be added to the service as a deploy key.

Command Access
The Apache user will need to be able to access git and optionionally drush from it’s path.

You will need sudo or root access so you can sudo -s then sudo -u <apache user> to switch to the Apache user and perform the needed commands.

Run “which git” and “which drush” as Apache to make sure that Apache does have access to the binaries. If not Apache needs to have the paths made available. This can be done in the environment variables, Ubuntu flavors: /etc/apache2/envvars, RedHat flavors: /etc/sysconfig/httpd

Fix example.
sudo nano -w /etc/apache2/envvars

Add this line to the end of the file.
export PATH=$PATH:/opt/local/bin

Drush integration is needed for clearing caches and schema update so “drush updb” is a default. Drush features-revert-all is volatile but it’s an option I’ve included. Be sure to leave it disabled in most cases however.

Apache SSH and Deploy Key Setup

Figure out Apache’s home directory. You can check the /etc/passwd if you need to. Cat the file and look for your apache user.
$ cat /etc/passwd
...
ftp:x:14:50:FTP User:/var/ftp:/sbin/nologin
nobody:x:99:99:Nobody:/var/www:/sbin/nologin
nscd:x:28:28:NSCD Daemon:/:/sbin/nologin
vcsa:x:69:69:virtual console memory owner:/dev:/sbin/nologin
...

This shows us that the home for nobody is /var/www. If home is set to just the root ”/” you should create a new home and move the user to it.
sudo mkdir /var/www/.ssh
sudo usermod -d /var/www apache

Change this directory's ownership to the Apache user (nobody, apache, etc).
sudo chown -R apache:apache /var/www/.ssh/

Generate a deploy key for the apache user. Do not set a passphrase!
sudo -Hu apache ssh-keygen

Confirm the key was created.
sudo cat /var/www/.ssh/id_rsa.pub

Copy this new public key over to the repo service.

Bitbucket
For Bitbucket the deploy key is added through repo settings page, e.g.  https://bitbucket.org/mediacurrent/<repo-name>/admin/deploy-keys

Github
For Github the deploy key is added through repo settings page, e.g.
https://github.com/chales/<repo-name>/settings/keys

Issues

There are a few quirks with system paths that need more testing. On my local Git works if the the full path is specified but Drush will not work properly. On staging (CentOS + XAMPP) everything works fine as does an Ubuntu 12.04.

Further Development

Rollback - setup for a 1 click rollback of the last update.

Setup the rest of the deploy options so that in a production setting a git clone is made into a dated directory and everything is symlinked.

To Do

General cleanup of code and documentation.
Verify git commands and exact desired functionality.
Confirm git submodule options are accurate.
Refine the requirement checks a little more.
Abstract for multiple checkouts, hook into CI service/script.
Better Drush options such as ability to add an array of commands.
Check/set perms on the script and log file, basically run a systems check.
Global (Drupal) permissions check/reset? Current is just for /.git
Refine error logging and setup of accurate log levels. Add microtimes to _deploy_log() for checking run times.
Make the script OS agnostic, this is mainly Windows slashes.
Modularize it? Logging could be sent to watchdog and the status page showing the last deploy.
Add a diff as a first step to skip making any script execution even if the branches match.
