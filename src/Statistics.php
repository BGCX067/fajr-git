<?php
/**
 * This file contains the object that handles basic statistics about fajr
 * execution
 *
 * @copyright  Copyright (c) 2010, 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr;

use libfajr\base\SystemTimer;
use libfajr\base\Timer;
use libfajr\connection\StatsConnection;
use libfajr\connection\HttpConnection;
use libfajr\connection\RequestStatistics;
use libfajr\base\IllegalStateException;

/**
 * Class for gathering some basic statistics about fajr run
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class Statistics
{
  /** @var Statistics $instance */
  private static $instance = null;

  /* TODO document */
  public static function getInstance()
  {
    if (!isset(self::$instance)) {
      self::$instance = new Statistics(SystemTimer::getInstance());
    }
    return self::$instance;
  }

  /** @var Timer $timer */
  private $timer = null;

  /** @var RequestStatistics */
  private $rawStats = null;

  /** @var RequestStatistics */
  private $finalStats = null;

  /** @var StatsConnection $allConnection */
  private $finalConnection = null;

  /**
   * Construct a new Statistics
   *
   * @param Timer $timer timer to use to measure time
   */
  public function __construct(Timer $timer)
  {
    $this->timer = $timer;
  }

  /**
   * @param RequestStatistics $stats
   * @returns void
   */
  public function setRawStatistics(RequestStatistics $stats)
  {
    $this->rawStats = $stats;
  }

  /**
   * Decorate a final underlying connection with ability to track
   * statistical information.
   *
   * @param HttpConnection $connection underlying connection to wrap
   * @returns HttpConnection decorated connection
   */
  public function hookFinalConnection(HttpConnection $connection)
  {
    if ($this->finalConnection !== null) {
      throw new IllegalStateException('Final connection already set');
    }
    $finalConnection = new StatsConnection($connection, new SystemTimer());
    $this->finalStats = $finalConnection->getStats();
    return $finalConnection;
  }

  /**
   * @returns int total number of executed requests
   */
  public function getRequestCount()
  {
    if ($this->finalStats === null) return 0;
    return $this->finalStats->getRequestCount();
  }

  /**
   * @returns int total number of downloaded and processed bytes
   */
  public function getDecodedByteCount()
  {
    if ($this->finalStats === null) return 0;
    return $this->finalStats->getDownloadedBytes();
  }

  /**
   * @returns int total number of downloaded unprocessed bytes
   */
  public function getDownloadedByteCount()
  {
    if ($this->rawStats === null) return 0;
    return $this->rawStats->getDownloadedBytes();
  }

  /**
   * @returns float (approximate) total execution time in seconds
   */
  public function getTotalTime()
  {
    return $this->timer->getElapsedTime();
  }

  /**
   * @returns float total network IO time spent in seconds
   */
  public function getTotalRequestTime()
  {
    if ($this->finalStats === null) return 0;
    return $this->finalStats->getTotalTime();
  }

}
