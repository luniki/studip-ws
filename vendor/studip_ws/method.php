<?php

/*
 * method.php - <short-description>
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

class Studip_Ws_Method {

  
  /**
   * <FieldDescription>
   *
   * @access private
   * @var <type>
   */
  var $service;

  
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
  var $expects;

  
  /**
   * <FieldDescription>
   *
   * @access private
   * @var <type>
   */
  var $returns;

  
  /**
   * <FieldDescription>
   *
   * @access private
   * @var <type>
   */
  var $description;

  
  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
	function Studip_Ws_Method(&$service, $name,
	                          $expects = NULL,
	                          $returns = NULL,
	                          $description = '') {

    # check $expects
    if (is_null($expects))
      $expects = array();
    else if (!is_array($expects)) {
      trigger_error('Third argument is expected to be an array.', E_USER_ERROR);
      exit;
    }

	  $this->service     =& $service;
	  $this->name        = $name;
	  $this->description = (string) $description;
	  $this->expects     = $expects;
	  $this->returns     = $returns;

    foreach ($this->expects as $key => $entry)
      $this->expects[$key] = Studip_Ws_Type::translate($entry);
    
    $this->returns = Studip_Ws_Type::translate($this->returns);
	}
}
