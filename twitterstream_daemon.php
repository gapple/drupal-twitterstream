<?php
/**
 * A Daemon script to set up a Streaming API consumer.
 */

// Make sure the daemon is not triggered from the web.
if (php_sapi_name() != 'cli') {
  exit();
}

if (preg_match('<(.*)/sites/[^/]+/modules/twitterstream>', dirname(__FILE__), $drupal_root)) {
  $drupal_root = $drupal_root[1];
}
else if (preg_match('<(.*)/profiles/[^/]+/modules/twitterstream>', dirname(__FILE__), $drupal_root)) {
  $drupal_root = $drupal_root[1];
}
else {
  exit("Could not find Drupal root directory\n");
}

define('DRUPAL_ROOT', $drupal_root);
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
// TODO don't require Phirehose and System_Daemon to be in the module directory
// System_Daemon can be installed through PEAR, Phirehose could be placed in
// the site's libraries folder.
require_once('phirehose/lib/Phirehose.php');
require_once('TwitterstreamPublicConsumer.php');
require_once('System_Daemon/System/Daemon.php');

// REMOTE_ADDR is not defined when called via CLI, but some Drupal functions
// assume that it exists, so define it to avoid undefined index errors.
$_SERVER['REMOTE_ADDR'] = null;

drupal_bootstrap(DRUPAL_BOOTSTRAP_VARIABLES);

// Find the correct UID and GID to run the daemon process under
$sysUser = posix_getpwnam(variable_get('twitterstream_daemon_user', 'www-data'));
if ($sysUser === FALSE || $sysUser['uid'] == 0) {
  exit("Configured user does not exist\n");
}
$sysGroup = array('gid' => $sysUser['gid']);
if (variable_get('twitterstream_daemon_group') != null) {
  $sysGroup = posix_getgrnam(variable_get('twitterstream_daemon_group'));
}
if ($sysGroup === FALSE || $sysGroup['gid'] == 0) {
  exit("Configured group does not exist\n");
}

System_Daemon::setOptions(array(
  'appName' => variable_get('twitterstream_daemon_name', 'twitterstream'),
  'appDescription' => "Consumes the Twitter Streaming API and stores tweets in the Drupal instance's database",

  'appRunAsUID' => $sysUser['uid'],
  'appRunAsGID' => $sysGroup['gid'],
));

// Since the database is opened in the parent process (required for
// variable_get() to retrieve the daemon settings), it will be closed when the
// parent process dies.  Close the connection so that the child process opens
// it's own when needed for the first time.
Database::closeConnection();


System_Daemon::start();

{
  $consumer = new TwitterstreamPublicConsumer(
      variable_get('twitterstream_username'),
      variable_get('twitterstream_password'),
      Phirehose::METHOD_FILTER
    );

  $consumer->db = Database::getConnection();

  // Set updates to occur less frequently than the Phirehose defaults.
  $consumer->setAvgPeriod(variable_get('twitterstream_status_period', 600));
  $consumer->setFilterCheckMin(variable_get('twitterstream_filter_check', 60));

  $consumer->consume();
}

System_Daemon::stop();

