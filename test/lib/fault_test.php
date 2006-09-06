<?php

/*
 * fault_test.php - Testing Studip_Ws_Fault
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'vendor/studip_ws/studip_ws.php';


class FaultTestCase extends UnitTestCase {

  function test_fault() {

    $this->expectError(new PatternExpectation('/Missing argument/'));
    new Studip_Ws_Fault();

    new Studip_Ws_Fault('hallo');
    new Studip_Ws_Fault(1);
    new Studip_Ws_Fault(1.4);
    new Studip_Ws_Fault(NULL);
    $this->assertNoErrors();

    $this->expectError(new PatternExpectation('/Array to string/'));
    new Studip_Ws_Fault(array());

    $this->expectError(new PatternExpectation('/Object to string/'));
    new Studip_Ws_Fault($this);
    
    $fault =& new Studip_Ws_Fault('hallo');
    $this->assertEqual('hallo', $fault->get_message());
  }
}
