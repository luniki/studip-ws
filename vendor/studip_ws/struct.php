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
   * <FieldDescription>
   *
   * @access private
   * @var <type>
   */
  var $members;

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
	function Studip_Ws_Struct() {
	  $this->members = array();
	}
	
	
	/**
	 * <MethodDescription>
	 *
	 * @param type <description>
	 * @param type <description>
	 * @param type <description>
	 *
	 * @return type <description>
	 */
	function add_member($name, $type, $options = NULL) {
    $member = array();
    $member['type'] = Studip_Ws_Api::translate_signature_entry($type);
    $member['options'] = $options;
    Studip_Ws_Struct::members($name, $member);
	}

  function members($key = NULL, $value = NULL) {
    static $members;
    
    if (is_null($members))
      $members = array();
    
    if (!is_null($key))
      $members[$key] = $value;

    return $members;
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function init() {
  }
}
