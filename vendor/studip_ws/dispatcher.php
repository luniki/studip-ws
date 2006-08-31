<?php

/*
 * dispatcher.php - <short-description>
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
 * @package   <package>
 * @package   <package>
 *
 * @abstract
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id$
 */

class Studip_Ws_Dispatcher {

	
  /**
   * <FieldDescription>
   *
   * @access private
   * @var array
   */
  var $api_methods = array();
  

  /**
   * Constructor. Give an unlimited number of services' class names as
   * arguments.
   *
   * @param string $services,... an unlimited number of services' class names
   *
   * @return void
   */
  function Studip_Ws_Dispatcher($services/*, ... */) {
    
    foreach (func_get_args() as $service_name) {
      
      # not a service
      if (!class_exists($service_name) || !$this->is_a_service($service_name))
        continue;

      $service =& new $service_name();

      $api_methods = $service->get_api_methods();

      foreach ($api_methods as $method_name => $method) {
        
        if (isset($this->api_methods[$method_name])) {
          trigger_error(sprintf('Method %s already defined', $method_name),
                        E_USER_ERROR);
          return;
        }

        $this->api_methods[$method_name] =& $api_methods[$method_name];
      }
    }
  }


  /**
   * This method is called to verify the existence of a mapped function.
   *
   * @param string  the function's name
   *
   * @return boolean returns TRUE, if the dispatcher can invoke the given
   *                 function, FALSE otherwise
   */
  function responds_to($function) {
    return isset($this->api_methods[$function]);
  }


  /**
   * This method is responsible to call the given function with the given
   * arguments.
   *
   * @param string the name of the function to invoke
   * @param array an array of arguments
   *
   * @return mixed the return value of the invoked function
   */
  function &invoke($method0, $argument_array) {

    # find service that provides $method
    if (!isset($this->api_methods[$method0]))
      return $this->throw_exception('No service responds to "%s".', $method0);
      
    $service = $this->api_methods[$method0]->service;
    $method = Studip_Ws_Dispatcher::map_function($method0);

    # calling before filter
    $before = $service->before_filter($method0, $argument_array);

    # #### TODO ####
    if ($before === FALSE)
      return $this->throw_exception('TODO: before_filter');
    else if (is_a($before, 'Studip_Ws_Fault'))
      return $this->throw_exception($before->get_message());

    # call actual function
    $result =& call_user_func_array(array(&$service, $method), $argument_array);
    
    # calling after filter
    $service->after_filter($method0, $argument_array, $result);

    return $result; 
  }


  /**
   * <MethodDescription>
   *
   * @param mixed <description>
   *
   * @return bool <description>
   *
   * @todo Should not this be elsewhere?
   */
  function is_a_service($class) {
    
    if (!is_string($class)) {
      if (is_object($class)) {
        $class = get_class($class);
      } else {
        trigger_error('Argument has to be a string or an object.', 
                      E_USER_ERROR);
        return FALSE;
      }
    }
      
    if (strcasecmp($class, 'Studip_Ws_Service') === 0)
      return TRUE;

    if ($parent = get_parent_class($class))
      return Studip_Ws_Dispatcher::is_a_service($parent);
    
    return FALSE;
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return string <description>
   */
  function map_function($function) {
    return $function . '_action';
  }
}
