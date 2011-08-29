<?php
/**
 *
 * @copyright  Copyright (c) 2010, 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 * @filesource
 */
namespace fajr;

use fajr\config\ServerConfig;
use fajr\config\FajrConfig;
use fajr\exceptions\SecurityException;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\connection\AIS2ServerConnection;
use fajr\libfajr\pub\login\CosignServiceCookie;
use fajr\libfajr\pub\login\LoginFactory;
use fajr\Request;
use fajr\util\FajrUtils;
use sfStorage;
use fajr\libfajr\login\CosignProxyLogin;

class ServerManager
{
  private $request;
  private $session;
  private $response;
  /** @var FajrConfig */
  private $config;

  public function __construct(sfStorage $session, Context $context, FajrConfig $config)
  {
    $this->session = $session;
    $this->request = $context->getRequest();
    $this->config = $config;
  }
  
  /**
   *
   * @return ServerConfig
   */
  public function getActiveServer()
  {
    $request = $this->request;
    $session = $this->session;

    $serverList = $this->config->get('AIS2.ServerList');
    $serverName = $this->config->get('AIS2.DefaultServer');

    if (($server = $session->read('server')) !== null) {
      //if ($session->read('login/login.class') === null) {
      //  throw new Exception('Fajr is in invalid state. Delete cookies and try again.');
      //}
      return $server;
    }

    if ($request->getParameter("serverName")) {
      $serverName = $request->getParameter("serverName");
      if (!isset($serverList[$serverName])) {
        throw new SecurityException("Invalid serverName!");
      }
    }
    
    assert(isset($serverList[$serverName]));
    return $serverList[$serverName];
  }
  
}