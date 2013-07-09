TO DO!!!

Git rollback 1 revision for reverts.

Actual deploy which would clone a new repo into a dated directory and symlink everything.


common.php -

Modify logging to include microtimes for starts. (see _deploy_log())

Break up the requirements checks into system, git, and drush. Possibly abstract more for various VCS.

Split up the requirements checks per app.

git-pull.php -

Split out the Drush options from gitpull_deploy(). Make this specific to git and create a separate Drush command file.
