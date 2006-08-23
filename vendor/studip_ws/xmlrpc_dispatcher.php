<?php

/*
 * xmlrpc_dispatcher.php - <short-description>
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
 * @package   studip
 * @package   ws
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id$
 */
class Studip_Ws_XmlrpcDispatcher extends Studip_Ws_Dispatcher {
  

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function dispatch($msg = NULL) {
    
    # ensure correct invocation
    if (is_null($msg) || !is_a($msg, 'xmlrpcmsg'))
      return $this->throw_exception('functions_parameters_type must not be '.
                                    'phpvals.');

    # get decoded parameters
    $len = $msg->getNumParams();
    $argument_array = array();
    for ($i = 0; $i < $len; ++$i)
      $argument_array[] = php_xmlrpc_decode($msg->getParam($i));

    # return result
    return new xmlrpcresp(
      php_xmlrpc_encode($this->invoke($msg->method(), $argument_array))); 
  }
  

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function throw_exception($message/*, ...*/) {
    $args = func_get_args();
    return new xmlrpcresp(0, $GLOBALS['xmlrpcerruser'] + 1,
                          vsprintf(array_shift($args), $args));
  }


  /**
   * Class method that composes the dispatch map from the available methods.
   *
   * @return array This service's dispatch map.
   *
   */
  function get_dispatch_map() {
    $dispatch_map = array();
    foreach ($this->api_methods as $method_name => $method)
      $dispatch_map[$method_name] = $this->map_method($method);
    return $dispatch_map;
  }
  
  
  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function map_method($method) {

    # TODO validate method

    ## 1. function
    $function = array(&$this, 'dispatch');

    ## 2. signature
    $signature = array(array());

    # return value
    $signature[0][] = $this->translate_type($method->returns);

    # arguments
    foreach ($method->expects as $type)
      $signature[0][] = $this->translate_type($type);
      
    ## 3. docstring
    $docstring = $method->description;

    return compact('function', 'signature', 'docstring');
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function translate_type($type) {

    # primitive types
    if (is_string($type))

      switch ($type) {
        case STUDIP_WS_TYPE_INT:
                                   return $GLOBALS['xmlrpcInt'];

        case STUDIP_WS_TYPE_STRING:
                                   return $GLOBALS['xmlrpcString'];

        case STUDIP_WS_TYPE_BASE64:
                                   return $GLOBALS['xmlrpcBase64'];

        case STUDIP_WS_TYPE_BOOL:
                                   return $GLOBALS['xmlrpcBoolean'];

        case STUDIP_WS_TYPE_FLOAT:
                                   return $GLOBALS['xmlrpcDouble'];

        case STUDIP_WS_TYPE_NULL:
                                   return $GLOBALS['xmlrpcBoolean'];
      }
    
    # complex types
    if (is_array($type))
    
      switch (key($type)) {

        case STUDIP_WS_TYPE_ARRAY:
                                   return $GLOBALS['xmlrpcArray'];

        case STUDIP_WS_TYPE_STRUCT:
                                   return $GLOBALS['xmlrpcStruct'];
      }

    trigger_error(sprintf('Type %s could not be found.', 
                          var_export($type, TRUE)),
                  E_USER_ERROR);
  }
}
