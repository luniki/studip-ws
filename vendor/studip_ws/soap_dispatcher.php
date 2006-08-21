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

class Studip_Ws_SoapDispatcher extends SoapServerDelegate {
	
	
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
  function Studip_Ws_SoapDispatcher($services/* ... */) {
    foreach (func_get_args() as $service)
      if (class_exists($service) && $this->is_a_service($service))
        $this->services[] = $service;
  }
  
  
  function is_a_service($class) {
    if (strcasecmp($class, 'Studip_Ws_Service')) return TRUE;
    if (!$class) return FALSE;
    return Studip_Ws_SoapDispatcher::is_a_service(get_parent_class($class));
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
      if (in_array($function, get_class_methods($service)))
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
  function invoke($function, $argument_array) {

    $function = Studip_Ws_SoapDispatcher::map_function($function);

    # iterate all services
    foreach ($this->services as $service) {

      # found service that provides $function
      if (in_array($function, get_class_methods($service))) {
      
        $service =& new $service();
      
        # calling before filter
        $before = $service->before_filter($function, $argument_array);
        # #### TODO ####
        if ($before === FALSE)
          return new soap_fault('Client', '', 'TODO: before_filter');
        else if (is_a($before, 'Studip_Ws_Fault'))
          return new soap_fault('Client', '', $before->get_message());

        # call actual function
        $result =& call_user_func_array(array(&$service, $function), $argument_array);
        
        # calling after filter
        $service->after_filter($function, $argument_array, $result);
        
        return $result;
      }
    }

    return new soap_fault('Server', '', 'Could not find function.');
  }
}
