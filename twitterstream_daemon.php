<?php
/**
 * A Daemon script to set up a Streaming API consumer.
 */

// Make sure the deamon is not triggered from the web.
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
  exit("Could not find Drupal root directory");
}

define('DRUPAL_ROOT', $drupal_root);
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
require_once('Phirehose.php');
require_once('TwitterstreamPublicConsumer.php');

// REMOTE_ADDR is not defined when called via CLI, but some Drupal functions
// assume that it exists, so define it to avoid undefined index errors.
$_SERVER['REMOTE_ADDR'] = null;

drupal_bootstrap(DRUPAL_BOOTSTRAP_VARIABLES);

$consumer = new TwitterstreamPublicConsumer(
    variable_get('twitterstream_username'),
    variable_get('twitterstream_password'),
    Phirehose::METHOD_FILTER
  );

$consumer->db = Database::getConnection();

$consumer->consume();
