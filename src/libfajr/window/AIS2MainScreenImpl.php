<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Represents "start" screen of AIS.
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Window
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\window;

use fajr\libfajr\pub\window\AIS2MainScreen;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\connection\AIS2ServerConnection;
use fajr\libfajr\base\DisableEvilCallsObject;
use fajr\libfajr\data_manipulation\AIS2VersionParser;
use fajr\libfajr\data_manipulation\AIS2ApplicationAvailabilityParser;
/**
 * Represents main page of AIS.
 *
 * @package    Fajr
 * @subpackage Libfajr__Window
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class AIS2MainScreenImpl extends DisableEvilCallsObject implements AIS2MainScreen 
{
  /** @var AIS2ServerConnection */
  private $connection;

  public function __construct(AIS2ServerConnection $connection)
  {
    $this->connection = $connection;
  }

  /**
   * Returns AIS version parsed from main page.
   *
   * @returns AIS2Version
   */
  public function getAisVersion(Trace $trace)
  {
    $versionParser = new AIS2VersionParser();
    $simpleConn = $this->connection->getSimpleConnection();
    $urlMap = $this->connection->getUrlMap();

    $html = $simpleConn->request($trace->addChild('requesting AIS2 main page'),
                                 $urlMap->getStartPageUrl());
    return $versionParser->parseVersionStringFromMainPage($html); 
  }

  /**
   * Get all applications which user can see in
   * AIS menu.
   *
   * @returns array(string)
   */
  public function getAllAvailableApplications(Trace $trace)
  {
    $appParser = new AIS2ApplicationAvailabilityParser();
    $simpleConn = $this->connection->getSimpleConnection();
    $urlMap = $this->connection->getUrlMap();

    // TODO(ppershing): make this more configurable
    $aisModules = array('SP', 'LZ', 'ES', 'ST', 'RH', 'UB', 'AS', 'RP');
    $applications = array();
    foreach ($aisModules as $module) {
      $childTrace = $trace->addChild('Listing applications for module '.$module);
      $html = $simpleConn->request($childTrace->addChild('Requesting page'),
                                   $urlMap->getChangeModulePage($module));
      $moduleApp = $appParser->findAllApplications($html);
      $childTrace->tlogVariable('applications', $moduleApp);
      $applications = array_merge($applications, $moduleApp);
    }

    // remove duplicates
    return array_values($applications);
  }
}
?>
