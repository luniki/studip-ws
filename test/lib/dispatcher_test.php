<?php

/*
 * dispatcher_test.php - Testing Studip_Ws_Dispatcher
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


class DispatcherTestCase extends UnitTestCase {

  function setUp() {
  }
  
  function tearDown() {
  }

  function test_is_a_service() {
    $is_not_a_service = Studip_Ws_Dispatcher::is_a_service('foo');
    $this->assertFalse($is_not_a_service);

    $is_not_a_service = Studip_Ws_Dispatcher::is_a_service(__CLASS__);
    $this->assertFalse($is_not_a_service);

    $is_not_a_service = Studip_Ws_Dispatcher::is_a_service($this);
    $this->assertFalse($is_not_a_service);

    $is_a_service = Studip_Ws_Dispatcher::is_a_service('Studip_Ws_Service');
    $this->assertTrue($is_a_service);

    $is_a_service = Studip_Ws_Dispatcher::is_a_service('FooService');
    $this->assertTrue($is_a_service);

    $is_a_service = Studip_Ws_Dispatcher::is_a_service(new Studip_Ws_Service());
    $this->assertTrue($is_a_service);

    $is_a_service = Studip_Ws_Dispatcher::is_a_service(new FooService());
    $this->assertTrue($is_a_service);
  }
}
