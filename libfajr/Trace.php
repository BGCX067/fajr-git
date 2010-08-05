<?php
/* {{{
Copyright (c) 2010 Peter Perešíni

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
 * Provides support for logging debug information into
 * tree structure called trace.
 *
 * PHP version 5.3.0
 *
 * @package    fajr
 * @subpackage libfajr
 * @author     Peter Peresini <ppershing@fks.sk>
 * @filesource
 */
namespace fajr\libfajr;

/**
 * Interface to objects tracing debug information.
 *
 * You can pass this object along your call tree
 * and log important information alogn with it.
 *
 * @package fajr
 * @subpackage libfajr
 * @author     Peter Peresine <ppershing@fks.sk>
 */
interface Trace {
  /**
   * Set the header of the current trace.
   *
   * @param string $header Header.
   *
   * @return void
   */
  public function setHeader($header);

  /**
   * Append a new text node into tracer.
   *
   * @param string $text Text to append.
   *
   * @return void
   */
  public function tlog($text);

  /**
   * Append a new data node into tracer. Data nodes are usually bigger than
   * text nodes and contains data that should retain formatting.
   *
   * @param string $string_data Data to append.
   *
   * @return void
   */
  public function tlogData($string_data);

  /**
   * Create a new child node of this trace object and return it.
   *
   * @param string $header Optional child header
   *
   * @return Trace Newly created child node.
   *
   */
  public function addChild($header = "");
}

/**
 * Represents tracer object which does nothing. It shoud be convenient to
 * pass around as default valeu of trace parameter.
 *
 * @package fajr
 * @subpackage libfajr
 */
class NullTrace implements Trace {
  public function setHeader($header) {}
  public function tlog($text) {}
  public function tlogData($text) {}
  public function addChild($header = "") {return new NullTrace();}
}