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
   * An array of services that are known to the delegate.
   *
   * @access private
   * @var array
   */
  var $services = array();


  /**
   * <FieldDescription>
   *
   * @access private
   * @var <type>
   */
  var $msg = NULL;


  /**
   * Constructor. Give an unlimited number of services' class names as
   * arguments.
   *
   * @param string $services,... an unlimited number of services' class names
   *
   * @return void
   */
  function Studip_Ws_XmlrpcDispatcher($services/*, ... */) {
    foreach (func_get_args() as $service)
      if (class_exists($service) && $this->is_a_service($service))
        $this->services[] = $service;
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
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function dispatch($msg = NULL) {
    
    # ensure correct invocation
    if (is_null($msg) || !is_a($msg, 'xmlrpcmsg'))
      return $this->throw_exception('functions_parameters_type must not be phpvals.');

     # store original msg
    $this->msg = $msg;

    # get original method
    list($service, $method) = explode('.', $msg->method() . '_action');

    # class responds to method?
    if (!is_callable(array($service, $method)))
      return $this->throw_exception('class "%s" does not respond to "%s".',
                                    $service, $method);

    # get decoded parameters
    $len = $msg->getNumParams();
    $argument_array = array();
    for ($i = 0; $i < $len; ++$i)
      $argument_array[] = php_xmlrpc_decode($msg->getParam($i));

    $service =& new $service();
  
    # calling before filter
    $before = $service->before_filter($method, $argument_array);
    # #### TODO ####
    if ($before === FALSE)
      return $this->throw_exception('TODO: before_filter');
    else if (is_a($before, 'Studip_Ws_Fault'))
      return $this->throw_exception($before->get_message());

    # call actual function
    $result =& call_user_func_array(array(&$service, $method), $argument_array);
    
    # calling after filter
    $service->after_filter($method, $argument_array, $result);

    return new xmlrpcresp(php_xmlrpc_encode($result)); 
  }


  /**
   * Class method that composes the dispatch map from the available methods.
   *
   * @return array This service's dispatch map.
   *
   */
  function get_dispatch_map() {

    $map = array();
    
    # iterate all services
    foreach ($this->services as $service) {
      foreach (get_class_methods($service) as $method) {
        if (preg_match('/^(\w+)_action$/', $method, $matches)) {
          $name = sprintf('%s.%s', strtolower($service), strtolower($matches[1]));
          $map[$name] = array('function' => array(&$this, 'dispatch'));
        }
      }
    }

    return $map;
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
