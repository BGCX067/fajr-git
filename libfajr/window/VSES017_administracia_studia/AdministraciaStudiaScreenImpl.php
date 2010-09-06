<?php
/* {{{
Copyright (c) 2010 Martin Králik

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.
}}} */

/**
 * TODO
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia
 * @author     Martin Kralik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\window\VSES017_administracia_studia;

use fajr\libfajr\pub\window\VSES017_administracia_studia\AdministraciaStudiaScreen;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\connection\SimpleConnection;
use fajr\libfajr\window\AIS2AbstractScreen;
use fajr\libfajr\window\RequestBuilderImpl;
use fajr\libfajr\window\ScreenRequestExecutor;
use fajr\libfajr\window\ScreenData;
use fajr\libfajr\data_manipulation\AIS2TableParser;
/**
 * Trieda reprezentujúca jednu obrazovku so zoznamom štúdií a zápisných listov.
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia
 * @author     Martin Kralik <majak47@gmail.com>
 */
class AdministraciaStudiaScreenImpl extends AIS2AbstractScreen
    implements AdministraciaStudiaScreen
{
  const APP_LOCATION_PATTERN = '@webui\(\)\.startApp\("([^"]+)","([^"]+)"\);@';

  protected $idCache = array();

  /**
   * @var AIS2TableParser
   */
  private $parser;

  public function __construct(Trace $trace, ScreenRequestExecutor $executor, AIS2TableParser $parser)
  {
    $data = new ScreenData();
    $data->appClassName = 'ais.gui.vs.es.VSES017App';
    $data->additionalParams = array('kodAplikacie' => 'VSES017');
    parent::__construct($trace, $executor, $data);
    $this->parser = $parser;
  }

  public function getZoznamStudii(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $response = $this->executor->requestContent($trace->addChild("get content"));
    return $this->parser->createTableFromHtml($trace->addChild("Parsing table"), $response, 'studiaTable_dataView');
  }

  public function getZapisneListy(Trace $trace, $studiumIndex)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->doRequest(
        $trace->addChild("Requesting data:"),
        array('compName' => 'nacitatDataAction',
              'objProperties' => array(
                'x' => -4,
                'y' => -4,
                'focusedComponent' => 'nacitatButton',
              ),
              'embObj' => array(
                'objName' => 'studiaTable',
                'dataView' => array(
                  'activeIndex' => $studiumIndex,
                  'selectedIndexes' => $studiumIndex,
                ),
              ),
            ));
    return $this->parser->createTableFromHtml($trace->addChild("Parsing table"),
        $data, 'VSES017_StudentZapisneListyDlg0_zapisneListyTable_dataView');
  }

  public function getZapisnyListIdFromZapisnyListIndex(Trace $trace, $zapisnyListIndex)
  {
    return $this->getIdFromZapisnyListIndex($trace, $zapisnyListIndex, 'idZapisnyList');
  }

  public function getStudiumIdFromZapisnyListIndex(Trace $trace, $zapisnyListIndex)
  {
    return $this->getIdFromZapisnyListIndex($trace, $zapisnyListIndex, 'idStudium');
  }

  protected function getIdFromZapisnyListIndex(Trace $trace, $zapisnyListIndex, $idType)
  {
    $this->openIfNotAlready($trace);
    if (empty($this->idCache[$zapisnyListIndex]))
    {
      $response = $this->executor->doRequest(
          $trace->addChild("Requesting data:"),
          array('compName' => 'terminyHodnoteniaAction',
                'objProperties' => array(
                  'x' => -4,
                  'y' => -4,
                  'focusedComponent' => 'runZapisneListyButton',
                ),
                'embObj' => array(
                  'objName' => 'zoznamTemTable',
                  'dataView' => array(
                    'activeIndex' => $zapisnyListIndex,
                    'selectedIndexes' => $zapisnyListIndex,
                  ),
                ),
              ));

      $data = $this->parseIdFromZapisnyListIndexFromResponse($response);
      if ($data == null) {
        throw new Exception("Neviem parsovať dáta z AISu");
      }
    
      $this->idCache[$zapisnyListIndex] = $data;
    } else {
      $trace->tlogVariable("data from cache", $this->idCache[$zapisnyListIndex]);
    }
    return $this->idCache[$zapisnyListIndex][$idType];
  }

  public function parseIdFromZapisnyListIndexFromResponse($response) {
      // FIXME: toto tunak spravit nejak krajsie
      $data = matchAll($response, self::APP_LOCATION_PATTERN, true);
      if ($data === false) return null;
      $data = matchAll($data[2], '@&idZapisnyList\=(?P<idZapisnyList>[0-9]*)&idStudium\=(?P<idStudium>[0-9]*)@', true);
      if ($data === false) return null;
      return removeIntegerIndexesFromArray($data);
  }

}
?>