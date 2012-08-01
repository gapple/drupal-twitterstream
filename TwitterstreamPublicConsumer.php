<?php

/**
 * Consume tweet data from the Steaming API and store in the database.
 */
class TwitterstreamPublicConsumer extends Phirehose {
  public $db = null;

  /**
   * Store the provided raw status to the database.
   *
   * @see Phirehose::enqueueStatus()
   */
  public function enqueueStatus($status) {
    $this->db->query("INSERT INTO {twitterstream_raw} SET data = :data", array(':data' => $status));
  }

  /**
   * Fetch the keywords to track and users to follow.
   *
   * @see Phirehose::checkFilterPredicates()
   */
  public function checkFilterPredicates() {

    $track = array();
    $follow = array();

    // TODO can we prevent querying the database and rebuilding the full arrays each time?
    $result = $this->db->query("SELECT module, params FROM {twitterstream_params}");
    foreach ($result as $row) {
      $params = unserialize($row->params);
      if (!empty($params['track'])) {
        $track = array_merge($track, $params['track']);
      }
      if (!empty($params['follow'])) {
        $follow = array_merge($follow, $params['follow']);
      }
    }

    $this->setTrack($track);
    $this->setFollow($follow);
  }

  /**
   * Pass log messages through to System_Daemon::log().
   *
   * @param string $message
   * @param string $level
   */
  public function log($message, $level = 'notice') {
    System_Daemon::log(System_Daemon::LOG_INFO, 'Phirehose: ' . $message);
  }
}
