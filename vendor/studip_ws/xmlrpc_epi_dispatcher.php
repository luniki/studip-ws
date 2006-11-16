<?php

/*
 * xmlrpc_epi_dispatcher.php - <short-description>
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * <ClassDescription>
 *

 * @package     studip
 * @subpackage  ws
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id$
 */

class Studip_Ws_XmlrpcEpiDispatcher extends Studip_Ws_Dispatcher {


  /**
   * <FieldDescription>
   *
   * @access private
   * @var <type>
   */
  var $server;


  /**
   * Constructor. Give an unlimited number of services' class names as
   * arguments.
   *
   * @param mixed $services,... an unlimited number or an array of class names
   *                            of services to include
   *
   * @return void
   */
  function Studip_Ws_XmlrpcEpiDispatcher($services = array() /*, ... */) {
    parent::Studip_Ws_Dispatcher(func_get_args());

    $this->server = xmlrpc_server_create();
    $this->register_methods();
  }


  /**
   * <MethodDescription>
   *
   * @return void
   */
  function register_methods() {
    foreach ($this->api_methods as $method_name => $method) {
      xmlrpc_server_register_method($this->server, $method_name,
                                    'xmlrpc_epi_call_cb');
    }
    xmlrpc_server_register_introspection_callback($this->server,
                                                  'xmlrpc_epi_introspect_cb');
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return void
   */
  function service($request_xml) {
    echo xmlrpc_server_call_method($this->server, $request_xml, $this);
    xmlrpc_server_destroy($this->server);
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return mixed <description>
   */
  function throw_exception($message/*, ...*/) {
    $args = func_get_args();
    return array(
      'faultCode'   => 17,  # TODO why 17?
      'faultString' => vsprintf(array_shift($args), $args));
  }


  /**
   * <MethodDescription>
   *
   * @param  mixed   <description>
   *
   * @return string  <description>
   */
  function translate_type($type0) {

    $map = array(STUDIP_WS_TYPE_INT    => 'int',
                 STUDIP_WS_TYPE_STRING => 'string',
                 STUDIP_WS_TYPE_BASE64 => 'base64',
                 STUDIP_WS_TYPE_BOOL   => 'boolean',
                 STUDIP_WS_TYPE_FLOAT  => 'double',
                 STUDIP_WS_TYPE_NULL   => 'boolean',
                 STUDIP_WS_TYPE_ARRAY  => 'array',
                 STUDIP_WS_TYPE_STRUCT => 'struct');

    $type = Studip_Ws_Type::get_type($type0);
    if (isset($map[$type]))
      return $map[$type];

    trigger_error(sprintf('Type %s could not be found.',
                          var_export($type, TRUE)),
                  E_USER_ERROR);
    return 'string';
  }
}


function xmlrpc_epi_call_cb($method_name, $params, &$dispatcher) {
  return $dispatcher->invoke($method_name, $params);
}


function xmlrpc_epi_introspect_cb(&$dispatcher) {
  ob_start();
  echo "<?xml version=\"1.0\"?>\n";
?><introspection version="1.0">
<methodList>
  <? foreach ($dispatcher->api_methods as $method_name => $method) : ?>
  <methodDescription name="<?= $method_name ?>">
    <purpose><?= $method->description ?></purpose>
    <signatures>
      <signature>
        <params>
          <? foreach ($method->expects as $type) : ?>
          <value type="<?= $dispatcher->translate_type($type) ?>" />
          <? endforeach ?>
        </params>
        <returns>
          <value type="<?= $dispatcher->translate_type($method->returns) ?>" />
        </returns>
      </signature>
    </signatures>
  </methodDescription>
  <? endforeach ?>
</methodList>
</introspection>
<?
  return ob_get_clean();
}
