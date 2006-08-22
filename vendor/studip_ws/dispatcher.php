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
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id$
 */
class Studip_Ws_Dispatcher {

	
  /**
   * An array of services that are known to the delegate.
   *
   * @access private
   * @var array
   */
  var $services = array();


  /**
   * Constructor. Give an unlimited number of services' class names as
   * arguments.
   *
   * @param string $services,... an unlimited number of services' class names
   *
   * @return void
   */
  function Studip_Ws_Dispatcher($services/*, ... */) {
    foreach (func_get_args() as $service)
      if (class_exists($service) && $this->is_a_service($service))
        $this->services[] =& new $service();
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   * @param type <description>
   *
   * @return type <description>
   */
  function &invoke($method0, $argument_array) {
    
    $method = Studip_Ws_Dispatcher::map_function($method0);

    # find service that provides $method
    $service = NULL;
    foreach ($this->services as $a_service)
      if (method_exists($a_service, $method)) {
        $service = $a_service;
        break;
      }
    if (is_null($service))
      return $this->throw_exception('No service responds to "%s".', $method0);


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

    return $result; 
  }


  /**
   * <MethodDescription>
   *
   * @param mixed <description>
   *
   * @return bool <description>
   */
  function is_a_service($class) {
    
    if (!is_string($class)) {
      if (is_object($class)) {
        $class = get_class($class);
      } else {
        trigger_error('Argument has to be a string or an object.', 
                      E_USER_ERROR);
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
   * @param type <description>
   *
   * @return type <description>
   */
  function map_function($function) {
    return $function . '_action';
  }
}
