<?php

/*
 * user_struct.php - Test structure.
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class UserStruct extends Studip_Ws_Struct {

  var $name, $id, $mentor;

  function UserStruct() {
    $this->add_element('name', 'string');
    $this->add_element('id',   1);
    $this->add_element('mentor', 'UserStruct');
    $this->add_element('mentors', array('int'));
  }
}
