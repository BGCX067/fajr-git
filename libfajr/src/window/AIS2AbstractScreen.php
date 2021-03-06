<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Window
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace libfajr\window;

use DOMXPath;
use AIS2Utils;
use DOMElement;
use DOMDocument;
use libfajr\trace\Trace;
use libfajr\window\LazyDialog;
use libfajr\data\ActionButton;
use libfajr\base\Preconditions;
use libfajr\login\AIS2LoginException;
use libfajr\base\IllegalStateException;
use libfajr\base\DisableEvilCallsObject;

/**
 * Abstraktná trieda reprezentujúca jednu obrazovku v AISe.
 *
 * @package    Libfajr
 * @subpackage Window
 * @author     Martin Králik <majak47@gmail.com>
 */
abstract class AIS2AbstractScreen extends DisableEvilCallsObject
    implements DialogParent, LazyDialog
{
  /**
   * String which identifies the screen
   *
   * @var string
   */
  private $formName = null;

  /**
   * So we know if window was opened already
   *
   * @var bool
   */
  protected $isOpen = false;

  /**
   * All data components which are in Window and we
   * want to use them..., where key is an ID of that
   * component
   *
   * @var array(string => ComponentInterface object)
   */
  public $components = null;

  /**
   * All action components which are in Window and we
   * want to use them..., where key is an ID of that
   * component
   *
   * @var array(string => ComponentInterface object)
   */
  protected $actions = null;

  protected $openedDialog = null;
  protected $trace = null;

  /**
   * Data information about this window (className...)
   *
   * @var ScreenData
   */
  protected $data = null;
  protected $executor = null;

  /**
   * Create window object and set some necessary information about it
   *
   * @param Trace $trace tracing tool for logs
   * @param ScreenRequestExecutor $executor execute requests and return response
   * @param ScreenData $data className....
   * @param array('dataComponents' => ComponentInterface      //eg.: DataTable
   *              'actionComponents' => ComponentInterface)  //eg.: actionButton
   */
  public function __construct(Trace $trace, ScreenRequestExecutor $executor, ScreenData $data, $components)
  {
    $this->executor = $executor;
    $this->trace = $trace;
    $this->data = $data;
    $this->components = $components['dataComponents'];
    $this->actions = $components['actionComponents'];
    $this->openWindow();
  }

  /**
   * Open window when we want to work with its
   * and initialize all components.
   *
   */
  public function openWindow()
  {
    if ($this->isOpen) {
      return;
    }
    $this->executor->requestOpen($this->trace->addChild("Opening window ".$this->data->appClassName), $this->data);
    $this->isOpen = true;
    $this->formName = $this->executor->formName;

    $response = $this->executor->requestContent($this->trace->addChild("get content"));
    $response = $this->prepareResponse($this->trace->addChild("Converting response from HTML to DOMDocument"), $response);

    $this->updateComponents($response, true);

  }

  /**
   * Basicly it make a request definied by component action with id $action
   *
   * @param string $action name, id of action
   * @return DOMDocument returns an response to that request
   */
  public function doAction($action)
  {
    assert(isset($this->actions[$action]));
    $button = $this->actions[$action];
    $action = new DOMDocument();

    //get part of xml action request
    $actionComponent = $button->getActionXML($this->formName);
    $actionComponent = $actionComponent->documentElement;

    $action->appendChild($action->importNode($actionComponent, true));

    $changedProps = new DOMDocument();
    $props = $changedProps->createElement("changedProps");
    $changedProps->appendChild($props);

    $changedProperties = $changedProps->createElement("changedProperties");
    $objName = $changedProps->createElement("objName", "app");
    $propertyValues = $changedProps->createElement("propertyValues");
    $nameValue = $changedProps->createElement("nameValue");
    $name = $changedProps->createElement("name", "activeDlgName");
    $value = $changedProps->createElement("value", $this->formName);

    $nameValue->appendChild($name);
    $nameValue->appendChild($value);
    $propertyValues->appendChild($nameValue);
    $changedProperties->appendChild($objName);
    $changedProperties->appendChild($propertyValues);

    $props->appendChild($changedProperties);

    $changedProperties2 = $changedProps->createElement("changedProperties");
    $objName2 = $changedProps->createElement("objName", $this->formName);
    $propertyValues2 = $changedProps->createElement("propertyValues");

    $props->appendChild($changedProperties);

    $changedProperties2 = $changedProps->createElement("changedProperties");
    $objName2 = $changedProps->createElement("objName", $this->formName);
    $propertyValues2 = $changedProps->createElement("propertyValues");

    $changedProperties2->appendChild($objName2);
    $changedProperties2->appendChild($propertyValues2);

    $embObjChProps = $changedProps->createElement("embObjChProps");

    //get a changed properites from data components
    foreach($this->components as $component){
      $change = $component->getStateChanges();
      $change = $change->documentElement;
      if($change){
        $embObjChProps->appendChild($changedProps->importNode($change, true));
      }
    }

    $changedProperties2->appendChild($embObjChProps);
    $props->appendChild($changedProperties2);
    $changedProps = $changedProps->documentElement;
    $action->appendChild($action->importNode($changedProps, true));

    //make a request
    $response = $this->executor->doActionRequest($this->trace, $action);
    $response = $this->prepareResponse($this->trace->addChild("Converting response from HTML to DOMDocument"), $response);

    //update component from response
    $this->updateComponents($response);

    return $response;
  }

  /**
   * Update all components after some action
   *
   * @param DOMDocument $dom response on some action
   * @param boolean $init if this is called from openWindow()
   */
  public function updateComponents($dom, $init = null)
  {
      if(empty($this->components)) return;
      foreach($this->components as $component){
          $component->updateComponentFromResponse($this->trace->addChild("updatujem"), $dom, $this->formName, $init);
      }
  }

  /**
   * Close window, because we won`t run out of Open windows limit
   *
   */
  public function closeWindow()
  {
    if (!$this->isOpen) {
      return;
    }

    assert($this->openedDialog == null);
    $this->executor->requestClose($this->trace);
    $this->isOpen = false;
  }

  /**
   * From HTML response make a DOMDocument
   *
   * @param Trace $trace trace for logging
   * @param string $html html response from AIS
   * @returns DOMDocument AIS response in DOMDocument
   */
  private function prepareResponse(Trace $trace, $html)
  {
    // fixing html code, so DOMDocumet it can parse
    Preconditions::checkIsString($html);
    $html = str_replace("<!--", "", $html);
    $html = str_replace("-->", "", $html);

    // just first javascript code contain data which we want
    $count = 1;
    $html = str_replace("type='application/javascript'", "id='init-data'", $html, $count);
    $html = str_replace("script", "div", $html);
    $html = $this->fixNbsp($html);

    $trace->tlogVariable("Fixed html", $html);

    // creating DOMDocument
    $dom = new DOMDocument();
    $trace->tlog("Loading html to DOM");
    $loaded = @$dom->loadHTML($html);
    if (!$loaded) {
      throw new ParseException("Problem parsing html to DOM.");
    }

    //fixing id atributes
    $trace->tlog('Fixing id attributes in the DOM');
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query("//*[@id]");
    foreach ($nodes as $node) {
      // Note: do not erase next line. @see
      // http://www.navioo.com/php/docs/function.dom-domelement-setidattribute.php
      // for explanation!
      $node->setIdAttribute('id', false);
      $node->setIdAttribute('id', true);
    }

    return $dom;
  }

  public function openDialogAndGetExecutor(Trace $trace, $dialogUid, DialogData $data)
  {
    $this->openWindow();
    if ($this->openedDialog != null) {
      throw new IllegalStateException('V AIS2 screene "'.$this->data->appClassName.
          '" už existuje otvorený dialog. Pre otvorenie nového treba pôvodný zatvoriť.');
    }
    $this->openedDialog = $dialogUid;
    return $this->executor->spawnDialogExecutor($data);
  }

  public function closeDialog($dialogUid)
  {
    if ($this->openedDialog != $dialogUid) {
      throw new IllegalStateException("Zatváram zlý dialóg!");
    }
    $this->openedDialog = null;
  }

  /**
   * Fix non-breakable spaces which were converted to special character during parsing.
   *
   * @param string $str string to fix
   *
   * @returns string fixed string
   */
  private function fixNbsp($str)
  {
    Preconditions::checkIsString($str);
    // special fix for &nbsp;
    // xml decoder decodes &nbsp; into special utf-8 character
    // TODO(ppershing): nehodili by sa tie &nbsp; niekedy dalej v aplikacii niekedy?
    $nbsp = chr(0xC2).chr(0xA0);
    return str_replace($nbsp, ' ', $str);
  }
}
?>
