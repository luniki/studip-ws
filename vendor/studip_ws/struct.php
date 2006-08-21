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
    $name = (string) $name;
    if (isset($this->struct_fields[$name]))
      trigger_error(sprintf('Element %s already defined.', $name),
                    E_USER_ERROR);

    # translate type description
    $type = Studip_Ws_Api::translate_signature_entry($type);

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
    
    if (strcasecmp($class, __CLASS__))
      return TRUE;
    
    if ($parent = get_parent_class($class))
      return Studip_Ws_Api::is_a_struct($parent);
    
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
   * @var <type>
   */
  var $name;
  
  /**
   * <FieldDescription>
   *
   * @access private
   * @var <type>
   */
  var $type;
  
  /**
   * <FieldDescription>
   *
   * @access private
   * @var <type>
   */
  var $options;

	/**
	 * <MethodDescription>
	 *
	 * @param type <description>
	 *
	 * @return type <description>
	 */
	function Studip_Ws_StructElement($name, $type, $options = array()) {
	  $this->name = (string) $name;
	  $this->type = $type;
	  $this->options = $options;
	}
}
