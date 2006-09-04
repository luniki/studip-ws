<?php

/*
 * struct_test.php - <short-description>
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


require_once 'vendor/studip_ws/studip_ws.php';

require_once 'test/fixtures/a_real_struct.php';
require_once 'test/fixtures/a_duck_typing_struct.php';
require_once 'test/fixtures/not_a_struct.php';


class StructTestCase extends UnitTestCase {

  function setUp() {
  }
  
  function tearDown() {
  }

  function test_elements_of_a_real_struct() {
    $elements = Studip_Ws_Type::get_struct_elements('ARealStruct');
    $this->assertEqual(sizeof($elements), 2);
    
    $real_name = current($elements);
    $this->assertIsA($real_name, 'Studip_Ws_StructElement');
    $this->assertEqual($real_name->name, 'real_name');
    $this->assertEqual(Studip_Ws_Type::get_type($real_name->type),
                       STUDIP_WS_TYPE_STRING);

    $real_id = next($elements);
    $this->assertIsA($real_id,   'Studip_Ws_StructElement');
    $this->assertEqual($real_id->name, 'real_id');
    $this->assertEqual(Studip_Ws_Type::get_type($real_id->type),
                       STUDIP_WS_TYPE_INT);
  }

  function test_elements_of_a_duck_typing_struct() {
    $elements = Studip_Ws_Type::get_struct_elements('ADuckTypingStruct');
    $this->assertEqual(sizeof($elements), 2);
    
    $real_name = current($elements);
    $this->assertIsA($real_name, 'Studip_Ws_StructElement');
    $this->assertEqual($real_name->name, 'duck_name');
    $this->assertEqual(Studip_Ws_Type::get_type($real_name->type),
                       STUDIP_WS_TYPE_STRING);

    $real_id = next($elements);
    $this->assertIsA($real_id,   'Studip_Ws_StructElement');
    $this->assertEqual($real_id->name, 'duck_id');
    $this->assertEqual(Studip_Ws_Type::get_type($real_id->type),
                       STUDIP_WS_TYPE_INT);
  }

  function test_elements_of_a_non_struct() {
    $elements = Studip_Ws_Type::get_struct_elements('NotAStruct');
    $this->assertEqual(sizeof($elements), 2);
    
    $real_name = current($elements);
    $this->assertIsA($real_name, 'Studip_Ws_StructElement');
    $this->assertEqual($real_name->name, 'not_a_struct_name');
    $this->assertEqual(Studip_Ws_Type::get_type($real_name->type),
                       STUDIP_WS_TYPE_STRING);

    $real_id = next($elements);
    $this->assertIsA($real_id,   'Studip_Ws_StructElement');
    $this->assertEqual($real_id->name, 'not_a_struct_id');
    $this->assertEqual(Studip_Ws_Type::get_type($real_id->type),
                       STUDIP_WS_TYPE_STRING);
  }
}
