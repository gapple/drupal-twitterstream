<?php

/**
 * Consume tweet data from the Steaming API and store in the database.
 */
class TwitterstreamPublicConsumer extends Phirehose {

  public $db = null;

  /**
   * Set the minimum period between writing status updates to the log.
   *
   * @param int $value
   *   Number of seconds
   */
  public function setAvgPeriod($value = 60) {
    $this->avgPeriod = $value;
  }

  /**
   * Set the minimum period between checking for changes to the filter
   * predicates.
   *
   * The stream is only updated at most every 120 seconds, even if this period
   * is shorter.
   *
   * @param int $value
   *   Number of seconds
   */
  public function setFilterCheckMin($value = 5) {
    $this->filterCheckMin = $value;
  }

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
   * Connects to the stream URL using the configured method.
   *
   * Phirehose::connect() only attempts to resolve the IP address of the
   * Streaming API endpoint once, so this method catches the exception and
   * attempts to connect again after a delay.
   *
   * @throws ErrorException
   *
   * @see Phirehose::connect()
   */
  protected function connect() {
    $networkFailures = 0;

    while (true) {
      try {
        parent::connect();
        return;
      }
      catch (PhirehoseNetworkException $ex) {

        $networkFailures++;

        if ($networkFailures >= 5) {
          throw $ex;
        }

        $this->log($ex->getMessage());

        sleep(pow(2, $networkFailures));
      }
    }
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
