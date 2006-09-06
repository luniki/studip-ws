<?php

/*
 * type_test.php - Testing Studip_Ws_Type.
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

define('COMPLEX_TYPE_START_HERE', 1000000);

class TypeTestCase extends UnitTestCase {

  function setUp() {
    $this->samples = array();
    $this->tcodes  = array();

    $this->samples[] = array('int', 'integer', 1, -1, 0);
    $tcode_int = array(STUDIP_WS_TYPE_INT => NULL);
    $this->tcodes[]  = $tcode_int;

    $this->samples[] = array('string', 'text', 'hallo', '');
    $this->tcodes[]  = array(STUDIP_WS_TYPE_STRING => NULL);

    $this->samples[] = array('base64');
    $this->tcodes[]  = array(STUDIP_WS_TYPE_BASE64 => NULL);

    $this->samples[] = array('bool', 'boolean', TRUE, FALSE);
    $this->tcodes[]  = array(STUDIP_WS_TYPE_BOOL => NULL);

    $this->samples[] = array('float', 'double', 1.0);
    $this->tcodes[]  = array(STUDIP_WS_TYPE_FLOAT => NULL);

    $this->samples[] = array('null', NULL);
    $this->tcodes[]  = array(STUDIP_WS_TYPE_NULL => NULL);

    $this->samples[COMPLEX_TYPE_START_HERE] = array(array('int'));
    $this->tcodes[COMPLEX_TYPE_START_HERE]  = array(STUDIP_WS_TYPE_ARRAY => $tcode_int);

    $this->samples[] = array(array(array('int')));
    $this->tcodes[]  = array(STUDIP_WS_TYPE_ARRAY => array(STUDIP_WS_TYPE_ARRAY => $tcode_int));

    $this->samples[] = array(__CLASS__);
    $this->tcodes[]  = array(STUDIP_WS_TYPE_STRUCT => __CLASS__);

    $this->samples[] = array('ARealStruct');
    $this->tcodes[]  = array(STUDIP_WS_TYPE_STRUCT => 'ARealStruct');
  }
  
  function tearDown() {
  }

  function test_translate() {
    foreach ($this->samples as $i => $sample)
      foreach ($sample as $code)
        $this->assertEqual(Studip_Ws_Type::translate($code), $this->tcodes[$i]);
  }

  function test_translate_fails() {
    $resource = opendir('.');
    $this->assertIsA($resource, 'resource');    
    $this->assertNotNull($resource);    
    
    $this->expectError(new PatternExpectation('/not a valid type/'));
    Studip_Ws_Type::translate($resource);
  }

  function test_get_type() {
    foreach ($this->samples as $i => $sample)
      foreach ($sample as $get_type => $code) {
        $translation = Studip_Ws_Type::translate($code);
        $type = Studip_Ws_Type::get_type($translation);
        $this->assertEqual($type, key($this->tcodes[$i]));
      }
    
    $this->expectError();
    Studip_Ws_Type::get_type('');
    
    $this->expectError();
    Studip_Ws_Type::get_type(1);
    
    $this->expectError();
    Studip_Ws_Type::get_type(TRUE);
    
    $this->expectError();
    Studip_Ws_Type::get_type(NULL);
    
    $this->expectError();
    Studip_Ws_Type::get_type($this);
  }

  function test_get_element_type() {
    foreach ($this->samples as $i => $sample)
      foreach ($sample as $get_type => $code) {
        $translation = Studip_Ws_Type::translate($code);
        $type = Studip_Ws_Type::get_element_type($translation);
        $this->assertEqual($type, current($this->tcodes[$i]));
      }

    
    $this->expectError();
    Studip_Ws_Type::get_element_type('');
    
    $this->expectError();
    Studip_Ws_Type::get_element_type(1);
    
    $this->expectError();
    Studip_Ws_Type::get_element_type(TRUE);
    
    $this->expectError();
    Studip_Ws_Type::get_element_type(NULL);
    
    $this->expectError();
    Studip_Ws_Type::get_element_type($this);
  }

  function test_is_complex() {
    foreach ($this->tcodes as $i => $tcode) {
      $is_complex = Studip_Ws_Type::is_complex_type($tcode);
      $this->assertEqual($i >= COMPLEX_TYPE_START_HERE, $is_complex);
    }
    
    $this->expectError();
    Studip_Ws_Type::is_complex_type('');
    
    $this->expectError();
    Studip_Ws_Type::is_complex_type(1);
    
    $this->expectError();
    Studip_Ws_Type::is_complex_type(TRUE);
    
    $this->expectError();
    Studip_Ws_Type::is_complex_type(NULL);
    
    $this->expectError();
    Studip_Ws_Type::is_complex_type($this);
  }

  function test_is_primitive() {
    foreach ($this->tcodes as $i => $tcode) {
      $is_complex = Studip_Ws_Type::is_primitive_type($tcode);
      $this->assertEqual($i < COMPLEX_TYPE_START_HERE, $is_complex);
    }
    
    $this->expectError();
    Studip_Ws_Type::is_primitive_type('');
    
    $this->expectError();
    Studip_Ws_Type::is_primitive_type(1);
    
    $this->expectError();
    Studip_Ws_Type::is_primitive_type(TRUE);
    
    $this->expectError();
    Studip_Ws_Type::is_primitive_type(NULL);
    
    $this->expectError();
    Studip_Ws_Type::is_complex_type($this);
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
