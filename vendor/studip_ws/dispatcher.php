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
	 * <MethodDescription>
	 *
	 * @param type <description>
	 *
	 * @return type <description>
	 */
	function Studip_Ws_Dispatcher() {
	  # code...
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
}
