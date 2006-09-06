<?php

/*
 * method_test.php - Testing Studip_Ws_Method.
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'vendor/studip_ws/studip_ws.php';

require_once 'test/fixtures/foo_service.php';


class MethodTestCase extends UnitTestCase {

  var $service;

  function setUp() {
    $this->service =& new FooService();
  }

  function test_constructor() {

    new Studip_Ws_Method($this->service, 'should_work');
    $this->assertNoErrors();

    new Studip_Ws_Method($this->service, 'should_work_too', NULL);
    new Studip_Ws_Method($this->service, 'should_work_too', array());

    $this->expectError();
    new Studip_Ws_Method($this->service, 'should_not_work', 1);
    $this->expectError();
    new Studip_Ws_Method($this->service, 'should_not_work', 1.5);
    $this->expectError();
    new Studip_Ws_Method($this->service, 'should_not_work', '1.7');
  }

  function test_service() {
    $method =& new Studip_Ws_Method($this->service, 'service_is_reference');
    $this->assertIsA($method->service, 'FooService');
    $this->assertReference($method->service, $this->service);
  }

  function test_name() {
    $method =& new Studip_Ws_Method($this->service, 'name_should_be_equal');
    $this->assertIsA($method->name, 'string');
    $this->assertEqual($method->name, 'name_should_be_equal');
  }

  function test_expects() {
    $method =& new Studip_Ws_Method($this->service, 'expects_is_an_array', array());
    $this->assertIsA($method->expects, 'Array');
    $this->assertEqual($method->expects, array());

    $method =& new Studip_Ws_Method($this->service, 'test_expects', array('int', 'string', 'float'));

    $int = Studip_Ws_Type::get_type(current($method->expects));
    $this->assertEqual($int, STUDIP_WS_TYPE_INT);

    $string = Studip_Ws_Type::get_type(next($method->expects));
    $this->assertEqual($string, STUDIP_WS_TYPE_STRING);

    $float = Studip_Ws_Type::get_type(next($method->expects));
    $this->assertEqual($float, STUDIP_WS_TYPE_FLOAT);
  }

  function test_returns() {
    $method =& new Studip_Ws_Method($this->service, 'return_should_be_equal', array(), 'string');
    $string = Studip_Ws_Type::get_type($method->returns);
    $this->assertEqual($string, STUDIP_WS_TYPE_STRING);
  }

  function test_description() {
    $method =& new Studip_Ws_Method($this->service,
                                    'description_should_be_equal',
                                    NULL, NULL,
                                    'description_should_be_equal');
    $this->assertEqual($method->description, 'description_should_be_equal');
  }
}
