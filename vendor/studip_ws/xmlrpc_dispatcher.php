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
class Studip_Ws_XmlrpcDispatcher {
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
  function is_a_service($class) {
    if (strcasecmp($class, 'Studip_Ws_Service')) return TRUE;
    if (!$class) return FALSE;
    return Studip_Ws_XmlrpcDispatcher::is_a_service(get_parent_class($class));
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
  
  function api_to_xmlrpc($api) {

    $dispatch_map = array();
    
    # iterate over api
    foreach ($api as $name => $options) {

      # validate options
      if (!isset($options['returns']))
        $options['returns'] = 'bool';


      $tmp = array();

      # get return type
      $signature = array();
      if (isset($options['returns']) && sizeof($options['returns']))
        $signature[] = $this->type_to_xmlrpc_def($options['returns']);
      else
        $signature[] = $GLOBALS['xmlrpcBoolean'];
      
      # get arguments
      if (isset($options['expects']) && sizeof($options['expects']))
        foreach ($options['expects'] as $type)
          $signature[] = $this->type_to_xmlrpc_def($type);
      
      $tmp['signature'] = array($signature);
      $tmp['function']  = array(&$this, 'dispatch');
      $tmp['docstring'] = isset($options['doc']) ? $options['doc'] : '';

      $dispatch_map[$name] = $tmp;
    }
    
    return $dispatch_map;
  }

  function type_to_xmlrpc_def($type) {

    # type: struct
#     if (FALSE)
#       trigger_error('not implemented yet.', E_USER_ERROR);

    # type: array
    if (is_array($type) || 'array' === $type)
      return $GLOBALS['xmlrpcArray'];

    # type: boolean
    if (is_bool($type) || preg_match('/(bool|boolean)/', $type))
      return $GLOBALS['xmlrpcBoolean'];
    
    # type: float
    if (is_float($type) || preg_match('/^(float|double)/', $type))
      return $GLOBALS['xmlrpcDouble'];
    
    # type: int
    if (is_int($type) || preg_match('/^(int|integer)$/', $type))
      return $GLOBALS['xmlrpcInt'];


    # type: base64
    if ('base64' === $type)
      return $GLOBALS['xmlrpcBase64'];

    # type: string
    if (is_string($type))
      return $GLOBALS['xmlrpcString'];


    trigger_error(sprintf('Type %s could not be found.', $type), E_USER_ERROR);
  }
}
