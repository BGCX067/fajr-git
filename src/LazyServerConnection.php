<?php
/**
 * Lazy server connection
 *
 * @copyright  Copyright (c) 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 * @filesource
 */
namespace fajr;

use fajr\libfajr\pub\connection\AIS2ServerConnection;
use fajr\libfajr\base\IllegalStateException;

class LazyServerConnection extends AIS2ServerConnection
{
  private $real;
  
  public function setReal(AIS2ServerConnection $real)
  {
    $this->real = $real;
  }
  
  private function getReal() {
    if ($this->real == null) {
      throw new IllegalStateException("Real server connection not set");
    }
    return $this->real;
  }
  
  function __construct()
  { 
  }
  
  public function getHttpConnection()
  {
    return $this->getReal()->getHttpConnection();
  }

  public function getSimpleConnection()
  {
    return $this->getReal()->getSimpleConnection();
  }

  public function getUrlMap()
  {
    return $this->getReal()->getUrlMap();
  }


}