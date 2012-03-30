<?php
/**
 * Tento súbor obsahuje objekt reprezentujúci odpoveď
 *
 * @copyright  Copyright (c) 2010-2012 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr;

use fajr\config\SkinConfig;
use libfajr\base\Preconditions;
use fajr\util\FajrUtils;

/**
 * Class for holding response information
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class Response
{
  /** @var Response $instance */
  private static $instance;

  /* TODO document */
  public static function getInstance()
  {
    if (!isset(self::$instance)) {
      self::$instance = new Response();
    }
    return self::$instance;
  }

  /** @var string template name to be used */
  private $template = null;
  
  /** @var string output format name*/
  private $format = 'xhtml';

  /** SkinConfig skin configuration */
  private $skin = null;

  protected $data = array('warnings' => array());

  /**
   * Set a variable to be available to the display subsystem
   * @param string $name Name of the variable to be available as
   * @param mixed $value Value
   */
  public function set($name, $value)
  {
    Preconditions::checkIsString($name, '$name should be string.');
    $this->data[$name] = $value;
  }

  /**
   * Return all data associated with this response as array
   *
   * @return array(key=>value) response data
   */
  public function getData()
  {
    return $this->data;
  }

  /**
   * Return a template name to be used
   *
   * @return string template name
   */
  public function getTemplate()
  {
    return $this->template;
  }

  /**
   * Set a template name to be used
   *
   * Note that the rendering subsystem may choose the actual template
   * used based on other parameters as well.
   *
   * @param string $template template name
   */
  public function setTemplate($template)
  {
    Preconditions::checkIsString($template, '$template should be string.');
    $this->template = $template;
  }
  
  public function getFormat()
  {
    return $this->format;
  }

  /**
   * Format of the response, will be used when choosing appropriate template
   * @param type $format 
   */
  public function setFormat($format)
  {
    Preconditions::checkIsString($format, 'Format must be string');
    $this->format = $format;
  }

  public function addWarning($message)
  {
    $this->data['warnings'][] = $message;
  }

  public function setSkin(SkinConfig $skin)
  {
    $this->skin = $skin;
  }

  public function getSkin()
  {
    return $this->skin;
  }

  /**
   * Sends redirect headers.
   *
   * Note that this will not end script execution!
   *
   * @param string $url absolute url as string
   * @param string $file file to which redirect
   * @todo set http response code to 302/303.
   *
   * @returns void
   */
  public function redirect($url)
  {
    Preconditions::checkIsString($url, '$url needs to be string');
    
    // Note: It is tempting to end script execution here.
    // However, it is not wise. Calling exit() will start
    // php shutdown phase and according to manual
    // there is unpredictable object destruction order
    // in this phase
    $this->setHeader('Location', $url);
    $this->set('redirectUrl', $url);
    $this->setTemplate('redirect');
  }

  /**
   * Set a response header
   * @param string $name
   * @param string $value
   */
  public function setHeader($name, $value)
  {
    Preconditions::checkIsString($name, '$name must be string');
    Preconditions::checkIsString($value, '$value must be string');
    header($name.': '.$value);
  }

  /**
   * Nastavi Strict-Transport-Security header.
   * 
   * Ak browser narazi na takyto header pokial je pripojeny cez HTTPS,
   * nebude povolovat HTTP requesty po dobu $expireTime, ale namiesto toho
   * pouzije rovno HTTPS.
   * 
   * @see https://developer.mozilla.org/en/Security/HTTP_Strict_Transport_Security
   * 
   * @param int $expireTime cas v sekundach kolko header plati
   * @param boolean $includeSubdomains ci sa vztahuje aj na poddomeny
   */
  public function setStrictTransportSecurity($expireTime, $includeSubdomains=false)
  {
    Preconditions::check(is_int($expireTime), '$expireTime must be integer');
    Preconditions::check(is_bool($includeSubdomains), '$includeSubdomains must be bool');
    $content = 'max-age=' . $expireTime;
    if ($includeSubdomains) {
      $content .= '; includeSubdomains';
    }
    $this->setHeader('Strict-Transport-Security', $content);
  }

  /**
   * Clear a cookie.
   *
   * @param string $cookieName name of the cookie to clear
   * @param string|null $path optional cookie path
   * @param string|null $domain optional domain name
   * @param boolean $secure whether the cookie should be only be transmitted
   *                        via HTTPS
   */
  public function clearCookie($cookieName, $path=null, $domain=null,
      $secure=true)
  {
    Preconditions::checkIsString($cookieName, '$cookieName must be string');
    // UNIX timestamp 1 should be far enough in past to trigger cookie
    // removal
    setCookie( $cookieName, "null", 1, $path, $domain, $secure );
  }
}
