<?php

/*
 * a_duck_typing_struct.php - <short-description>
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


class ADuckTypingStruct {

  function get_struct_elements() {
    $elements = array();
    $elements[] =& new Studip_Ws_StructElement('duck_name', 'string');
    $elements[] =& new Studip_Ws_StructElement('duck_id',   'int');
    return $elements;
  }
}
