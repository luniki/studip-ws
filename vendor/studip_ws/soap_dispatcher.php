<?php

/*
 * soap_dispatcher.php - Delegate for Stud.IP SOAP Server.
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

class Studip_Ws_SoapDispatcher extends Studip_Ws_Dispatcher
                            /* implements SoapServerDelegate */ {


  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return bool <description>
   */
  function responds_to($function) {
  
    $function = Studip_Ws_SoapDispatcher::map_function($function);
  
    foreach ($this->services as $service)
      if (method_exists($service, $function))
        return TRUE;

    return FALSE;
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
    return new soap_fault('Client', '', vsprintf(array_shift($args), $args));
  }


  /**
   * <MethodDescription>
   *
   * @param mixed <description>
   *
   * @return array <description>
   */
  function map_service(&$service) {
  
    $mapping = array();
    
    # iterate over api
    foreach ($service->get_api_methods() as $name => $method) {
      $mapping[$name] = $this->map_service_method($method); 
    }
    
    return $mapping;  
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function map_service_method($method) {

    # TODO validate method


    ## 1. function
    $function = array(&$this, 'dispatch');

    ## 2. signature
    $signature = array();

    # return value
    $signature[] = $this->translate_type($method['returns']);

    # arguments
    foreach ($method['expects'] as $type)
      $signature[] = $this->translate_type($type);

    ## 3. docstring
    $docstring = $method['description'];

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
