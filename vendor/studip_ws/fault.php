<?php

/*
 * fault.php - Abstraction of service faults
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

class Studip_Ws_Fault {
  /**
   * <FieldDescription>
   *
   * @access private
   * @var string
   */
  var $message;
  

  /**
   * Constructor.
   *
   * @param string <description>
   *
   * @return type <description>
   */
  function Studip_Ws_Fault($message) {
    $this->message = $message;
  }


  /**
   * Returns the faults message.
   *
   * @return string <description>
   */
  function get_message() {
  	return $this->message;
  }
}
