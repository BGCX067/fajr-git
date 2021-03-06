<?php
// Copyright (c) 2011 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This is where Fajr keeps its instance of a backend factory.
 *
 * @package    Fajr
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @filesource
 */

namespace fajr;

use fajr\config\ServerConfig;
use libfajr\BackendFactory;
use libfajr\FakeBackendFactory;
use libfajr\LibfajrBackendFactory;

class BackendProvider
{
  /** @var BackendFactory $instance */
  private static $instance;

  public static function getInstance()
  {
    if (!isset(self::$instance)) {
      $server = ServerManager::getInstance()->getActiveServer();
      switch ($server->getBackendType()) {
        case ServerConfig::BACKEND_FAKE:
          self::$instance = new FakeBackendFactory(SessionStorageProvider::getInstance());
          break;

        case ServerConfig::BACKEND_LIBFAJR:
          self::$instance = new LibfajrBackendFactory(LazyServerConnection::getInstance());
          break;

        default:
          assert(false);
      }
    }
    return self::$instance;
  }
}

