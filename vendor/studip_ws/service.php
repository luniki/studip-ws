<?php

/*
 * service.php - Abstract super class of all Stud.IP webservices.
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * This class is the abstract superclass of all available Stud.IP webservices.
 * You have to extend it when implementing your own webservice.
 *
 * @package   studip
 * @package   ws
 *
 * @abstract
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id$
 */

class Studip_Ws_Service {
  
  /**
   * <FieldDescription>
   *
   * @access private
   * @var <type>
   */
  var $api_methods = array();


  /**
   * Constructor.
   *
   * @return void
   */
  function Studip_Ws_Service() {
  }


  /**
   * This method is called before every other service method.
   *
   * @param string the function's name.
   * @param array an array of arguments that will be delivered to the function.
   *
   * @return mixed if this method returns a "TODO_fault" or "FALSE", further
   *               processing will be aborted and a "TODO_fault" delivered.
   */
  function before_filter($name, &$args) {
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   * @param array <description>
   * @param mixed <description>
   *
   * @return void
   */
  function after_filter($name, &$args, &$result) {
  }

  
  /**
   * <MethodDescription>
   *
   * @param string <description>
   * @param array  <description>
   * @param mixed  <description>
   * @param string <description>
   *
   * @return void
   */
  function add_api_method($name, $arguments = NULL, $returns = NULL,
                          $description = NULL) {

    # check $name
# TODO
#    if (!method_exists($this, $name . '_action'))
#      trigger_error(sprintf('No such method exists: %s.', $name), E_USER_ERROR);
    if (isset($this->api_methods[$name]))
      trigger_error(sprintf('Method %s already added.', $name), E_USER_ERROR);
    
    # check $arguments
    if (is_null($arguments))
      $arguments = array();
    else if (!is_array($arguments))
      trigger_error('Second argument is expected to be an array.',
                    E_USER_ERROR);

    # check $description
    $description = (string) $description;

    $expects = array();
    foreach ($arguments as $entry) {
      $expects[] = Studip_Ws_Api::translate_signature_entry($entry);
    }
    
    $returns = Studip_Ws_Api::translate_signature_entry($returns);
    
    return $this->api_methods[$name] =
      compact('expects', 'returns', 'description');
  }
  
  
  /**
   * <MethodDescription>
   *
   * @return array <description>
   */
  function &get_api_methods() {
    return $this->api_methods;
  }
  
  
  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function &get_api_method($name) {
    if (!isset($this->api_methods[$name])) {
      $null_by_reference = NULL; return $null_by_reference;
    }
      
    return $this->api_methods[$name];
  }
  
  /**
   * <MethodDescription>
   *
   * @return void
   */
  function clear_api_methods() {
    $this->api_methods = array();
  }
}
