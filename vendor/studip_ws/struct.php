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
   * Holds the struct's fields.
   *
   * @access private
   * @var array
   */
  var $struct_fields = array();


	/**
	 * <MethodDescription>
	 *
	 * @param string <description>
	 * @param mixed <description>
	 * @param array <description>
	 *
	 * @return void
	 */
	function add_element($name, $type, $options = array()) {

    # name must not exist
    if (isset($this->struct_fields[$name])) {
      trigger_error(sprintf('Element %s already defined.', $name),
                    E_USER_ERROR);
      return NULL;
    }

    # TODO options

    $this->struct_fields[$name] =&
      new Studip_Ws_StructElement($name, $type, $options);
	}


  /**
   * <MethodDescription>
   *
   * @return array <description>
   */
  function &get_elements() {
    return $this->struct_fields;
  }

  
  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return bool <description>
   */
  function is_a_struct($class) {
    
    if (!is_string($class)) {
      if (is_object($class)) {
        $class = get_class($class);
      } else {
        trigger_error('Argument has to be a string or an object.',
                      E_USER_ERROR);
      }
    }
      
    if (strcasecmp($class, __CLASS__) === 0)
      return TRUE;

    if ($parent = get_parent_class($class))
      return Studip_Ws_Struct::is_a_struct($parent);
    
    return FALSE;
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
