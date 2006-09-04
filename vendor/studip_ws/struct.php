<?php

/*
 * struct.php - <short-description>
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
 * @abstract
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id$
 */

class Studip_Ws_Struct {


  /**
   * Class-level constructor. Initialize your struct within using
   * ClassName::add_element('name', 'type', $options)
   *
   * @abstract
   *
   * @return void
   */
  function init() {
  }

  
  /**
   * <MethodDescription>
   *
   * @param string <description>
   * @param mixed <description>
   * @param array <description>
   *
   * @return type <description>
   */
  function add_element($name = NULL, $type = NULL, $options = array()) {

    # static var setup
    static $elements;
    if (is_null($elements))
      $elements = array();

    # setter functionality
    if (!is_null($name)) {


      # no doublets
      if (isset($elements[$name])) {
        trigger_error(sprintf('Element %s already defined.', $name),
                      E_USER_ERROR);
        return;
      }
      
      # store it
      $elements[$name] =& new Studip_Ws_StructElement($name, $type, $options);

      return;
    }
    
    # getter functionality
    return $elements;    
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return array <description>
   */
  function &get_struct_elements($class = NULL) {

    static $once;

    # call 'init' once
    if (is_null($once)) {

      # guess class name if not given (does not work in PHP5 anymore?!)
      if (is_null($class)) {
        $backtrace = debug_backtrace();
        $class = $backtrace[0]['class'];
      }
  	
      # call "class" constructor
      call_user_func(array($class, 'init'));

      $once = call_user_func(array($class, 'add_element'));
    }

    return $once;
  }
}


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

class Studip_Ws_StructElement {


  /**
   * <FieldDescription>
   *
   * @access private
   * @var string
   */
  var $name;

  
  /**
   * <FieldDescription>
   *
   * @access private
   * @var mixed
   */
  var $type;
  
  
  /**
   * <FieldDescription>
   *
   * @access private
   * @var array
   */
  var $options;


  /**
   * <MethodDescription>
   *
   * @param string <description>
   * @param mixed  <description>
   * @param array  <description>
   *
   * @return void
   */
  function Studip_Ws_StructElement($name, $type, $options = array()) {
    $this->name    = (string) $name;
    $this->type    = Studip_Ws_Type::translate($type);
    $this->options = $options;
  }
}
