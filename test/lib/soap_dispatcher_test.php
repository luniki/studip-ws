<?php

/*
 * soap_dispatcher_test.php - Testing SOAP Dispatcher.
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */
 
require_once 'vendor/studip_ws/studip_ws.php';
require_once 'vendor/studip_ws/soap_dispatcher.php';

require_once 'vendor/nusoap/nusoap.php';

class SoapDispatcherTestCase extends UnitTestCase {

  var $service, $dispatcher;

  function setUp() {
    $this->dispatcher =& new Studip_Ws_SoapDispatcher();
  }
  
  function tearDown() {
    $this->dispatcher = NULL;
  }


  function test_type_to_name() {
  
    $int = Studip_Ws_Type::translate('int');
    $this->assertEqual($this->dispatcher->type_to_name($int), 'int');
  
    $string = Studip_Ws_Type::translate('string');
    $this->assertEqual($this->dispatcher->type_to_name($string), 'string');
  
    $boolean = Studip_Ws_Type::translate(TRUE);
    $this->assertEqual($this->dispatcher->type_to_name($boolean), 'boolean');

    $int = Studip_Ws_Type::translate(array(array('int')));
    $this->assertEqual($this->dispatcher->type_to_name($int), 'intArrayArray');

    $struct = Studip_Ws_Type::translate('UserStruct');
    $this->assertEqual($this->dispatcher->type_to_name($struct), 'UserStruct');

    $array_of_struct = Studip_Ws_Type::translate(array('UserStruct'));
    $this->assertEqual($this->dispatcher->type_to_name($array_of_struct), 'UserStructArray');
  }


  function test_type_to_name_wns() {
  
    $int = Studip_Ws_Type::translate('int');
    $this->assertEqual($this->dispatcher->type_to_name_wns($int), 'xsd:int');
  
    $string = Studip_Ws_Type::translate('string');
    $this->assertEqual($this->dispatcher->type_to_name_wns($string), 'xsd:string');
  
    $boolean = Studip_Ws_Type::translate(TRUE);
    $this->assertEqual($this->dispatcher->type_to_name_wns($boolean), 'xsd:boolean');

    $int = Studip_Ws_Type::translate(array(array('int')));
    $this->assertEqual($this->dispatcher->type_to_name_wns($int), 'tns:intArrayArray');

    $struct = Studip_Ws_Type::translate('UserStruct');
    $this->assertEqual($this->dispatcher->type_to_name_wns($struct), 'tns:UserStruct');

    $array_of_struct = Studip_Ws_Type::translate(array('UserStruct'));
    $this->assertEqual($this->dispatcher->type_to_name_wns($array_of_struct), 'tns:UserStructArray');
  }
  
  function test_something() {
    $this->fail('TODO');
  }
}
