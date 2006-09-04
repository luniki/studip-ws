<?php

/*
 * a_real_struct.php - <short-description>
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


class ARealStruct extends Studip_Ws_Struct {

  function init() {
    ARealStruct::add_element('real_name', 'string');
    ARealStruct::add_element('real_id',   'int');
  }
}
