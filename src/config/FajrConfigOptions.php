<?php
/**
 * Holds configuration options for FajrConfig
 * @copyright  Copyright (c) 2011-2012 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Config
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\config;

use fajr\validators\StringValidator;
use fajr\validators\ChoiceValidator;

/**
 * Holds info about all configurable options of FajrConfig
 *
 * @package    Fajr
 * @subpackage Config
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class FajrConfigOptions {
  const GOOGLE_ANALYTICS_ACCOUNT = 'GoogleAnalytics.Account';
  const DEBUG_BANNER = 'Debug.Banner';
  const DEBUG_TRACE = 'Debug.Trace';
  const DEBUG_TRACE_DIR = 'Debug.Trace.Directory';
  const DEBUG_TRACE_NONE = 'none';
  const DEBUG_TRACE_ARRAY = 'array';
  const DEBUG_TRACE_BINARY = 'binary';
  const DEBUG_TRACE_TEXT = 'text';
  const DEBUG_EXCEPTION_SHOWSTACKTRACE = 'Debug.Exception.ShowStacktrace';
  const PATH_TO_TEMP = 'Path.Temporary';
  const PATH_TO_COOKIES = 'Path.Temporary.Cookies';
  const PATH_TO_SESSIONS = 'Path.Temporary.Sessions';
  const AIS_SERVERLIST = 'AIS2.ServerList';
  const AIS_DEFAULT_SERVER = 'AIS2.DefaultServer';
  const PATH_TO_SSL_CERTIFICATES = 'SSL.CertificatesDir';
  const REQUIRE_SSL = 'SSL.Require';
  const STRICT_TRANSPORT_SECURITY = 'SSL.StrictRequire';
  const USER_AGENT = 'Connection.UserAgent';
  const PATH_TO_TEMPLATES = 'Template.Directory';
  const USE_CACHE = 'Cache.Enabled';
  const PATH_TO_CACHE = 'Cache.Path';
  const PATH_TO_TEMPLATE_CACHE = 'Template.Cache.Path';
  const PATH_TO_ROUTER_CACHE = 'Router.Cache.Path';
  const TEMPLATE_SKINS = 'Template.Skin.Skins';
  const TEMPLATE_DEFAULT_SKIN = 'Template.Skin.Default';
  const IS_DEVEL = 'Features.Devel';
  /** Unique instance ID on a given server, used e.g. for naming session cookies */
  const INSTANCE_NAME = 'Instance.Name';
  const WARNINGS = 'Warnings';

  /**
   * Return description of configuration parameters
   *
   * @returns array key=>
   *                 array('defaultValue'=>value, // if not present,
   *                                              // the param is required
   *                       'relativeTo'=>path, // for directories
   *                       'validator'=>validator // name of validator to use)
   * @see configuration.example.php for more information
   */
  public static function getParameterDescription()
  {

    $booleanValidator = new ChoiceValidator(array(true, false));
    $stringValidator = new StringValidator();
    $pathValidator = new StringValidator();

    $parameterDescription = array(
      self::GOOGLE_ANALYTICS_ACCOUNT =>
        array('defaultValue'=>null),

      self::DEBUG_BANNER =>
        array('defaultValue' => false,
              'validator' => $booleanValidator),

      self::DEBUG_TRACE =>
        array('defaultValue' => self::DEBUG_TRACE_NONE,
              'validator' => new ChoiceValidator(
                  array(self::DEBUG_TRACE_NONE, self::DEBUG_TRACE_ARRAY,
                    self::DEBUG_TRACE_BINARY, self::DEBUG_TRACE_TEXT))),

      self::DEBUG_TRACE_DIR =>
        array('defaultValue' => null,
              'relativeTo' => 'Path.Temporary',
              'validator' => $pathValidator),

      self::DEBUG_EXCEPTION_SHOWSTACKTRACE =>
        array('defaultValue' => false,
              'validator' => $booleanValidator),

      self::PATH_TO_TEMP =>
        array('defaultValue' => './temp',
              'validator' => $pathValidator),

      self::PATH_TO_COOKIES =>
        array('defaultValue' => './cookies',
              'relativeTo' => 'Path.Temporary',
              'validator' => $pathValidator),

      self::PATH_TO_SESSIONS =>
        array('defaultValue' => './sessions',
              'relativeTo' => 'Path.Temporary',
              'validator' => $pathValidator),

      self::AIS_SERVERLIST =>
        array(),

      self::AIS_DEFAULT_SERVER =>
        array('validator' => $stringValidator),

      self::PATH_TO_SSL_CERTIFICATES =>
        array('defaultValue' => null),

      self::REQUIRE_SSL =>
        array('defaultValue' => false,
              'validator' => $booleanValidator),

      self::STRICT_TRANSPORT_SECURITY =>
        array('defaultValue' => null),

      self::USER_AGENT =>
        array('defaultValue' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; sk; rv:1.9.1.7) Gecko/20091221 Firefox/3.5.7',
              'validator' => $stringValidator),

      self::PATH_TO_TEMPLATES =>
        array('defaultValue' => './templates',
              'validator' => $pathValidator),

      self::USE_CACHE =>
        array('defaultValue' => false,
              'validator' => $booleanValidator),
      
      self::PATH_TO_CACHE =>
        array('defaultValue' => './cache',
              'relativeTo' => self::PATH_TO_TEMP,
              'validator' => $pathValidator),

      self::PATH_TO_TEMPLATE_CACHE =>
        array('defaultValue' => './twig',
              'relativeTo' => self::PATH_TO_CACHE,
              'validator' => $pathValidator),
      
      self::PATH_TO_ROUTER_CACHE =>
        array('defaultValue' => './router',
              'relativeTo' => self::PATH_TO_CACHE,
              'validator' => $pathValidator),

      self::TEMPLATE_SKINS =>
        array(
            'defaultValue' => array(
              'noskin' => new SkinConfig(
                array(
                  'name' => 'noskin',
                  'internal' => true,
                  'path' => '',
                )),
              'fajr' => new SkinConfig(
                array(
                  'name' => 'default',
                  'path' => 'fajr',
                  'parent' => 'noskin'))
              )),

      self::TEMPLATE_DEFAULT_SKIN =>
        array('defaultValue' => 'fajr',
              'validator' => $stringValidator),

      self::IS_DEVEL =>
        array('defaultValue' => false,
              'validator' => $booleanValidator),
      
      self::INSTANCE_NAME =>
          array('defaultValue' => 'fajr',
                'validator' => $stringValidator),
      
      self::WARNINGS =>
          array('defaultValue' => array()),
    );

    return $parameterDescription;
  }

}
