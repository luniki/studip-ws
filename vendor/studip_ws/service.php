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
   * @param string <description>
   *
   * @return void
   */
  function add_api_method($name, $options, $description = '') {

    $description = (string) $description;

    if (!is_array($options)) $options = array();

    # if 'expects' is omitted, this method has no arguments
    if (!isset($options['expects']))
      $options['expects'] = array();

    if (!is_array($options['expects']))
      trigger_error('$options[\'expects\'] is expected to be an array.',
                    E_USER_ERROR);

    $expects = array();
    foreach ($options['expects'] as $entry) {
      $expects[] = Studip_Ws_Api::translate_signature_entry($entry);
    }
    
    
    # if 'returns' is omitted, this method has no return value
    if (!isset($options['returns']))
      $options['returns'] = NULL;
    
    $returns = Studip_Ws_Api::translate_signature_entry($options['returns']);

    
    $this->api_methods[$name] = compact('expects', 'returns', 'description');
  }
}
