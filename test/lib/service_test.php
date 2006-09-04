<?php

/*
 * api_test.php - <short-description>
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


require_once 'vendor/studip_ws/studip_ws.php';

require_once 'test/fixtures/user_struct.php';


class ServiceTestCase extends UnitTestCase {

  var $service;

  function setUp() {
    $this->service =& new Studip_Ws_Service();
  }
  
  function tearDown() {
  }


  function add_api_method($expects, $returns = NULL, $description = '') {
    $name = sprintf('function_in_line_%d',
                             next(current(debug_backtrace())));
    return $this->service->add_api_method($name, $expects, $returns,
                                          $description);
  }


  function assertDescription(&$sig, $expected_description) {
    $actual_description = $sig->description;
    $msg = sprintf('Descriptions do not match. Actual: "%s" Expected: "%s"',
                   var_export($actual_description, TRUE),
                   var_export($expected_description, TRUE));
    $this->assertEqual($actual_description, $expected_description);
  }

  function assertArgument(&$sig, $expected_type) {

    # any arguments left?
    if (sizeof($sig->expects) === 0) {
      if (is_null($expected_type))
        $this->pass();
      else
        $this->fail('No more arguments.');
      return;
    }
    
    $actual_type = Studip_Ws_Type::get_type(array_shift($sig->expects));

    $msg = sprintf('Types do not match. Actual: "%s" Expected: "%s"',
                   var_export($actual_type, TRUE),
                   var_export($expected_type, TRUE));
    $r = $this->assertEqual($actual_type, $expected_type, $msg);
  }
  
  function assertReturnValue(&$sig /*, ...*/) {

    $returns = $sig->returns;
    
    $args = func_get_args();
    $args = array_slice($args, 1);
    
    do {
      $actual_type   = Studip_Ws_Type::get_type($returns);
      $expected_type = current($args);
      $msg = sprintf('Types do not match. Actual: "%s" Expected: "%s"',
                     var_export($actual_type, TRUE),
                     var_export($expected_type, TRUE));
      $this->assertEqual($actual_type, $expected_type, $msg);
      $returns = Studip_Ws_Type::get_element_type($returns);
    } while (!is_null($returns) && next($args) !== FALSE);
  }


  function test_api_signatures_description() {
    $sig = $this->add_api_method(NULL, NULL, 'foobar');
    $this->assertDescription($sig, 'foobar');
  }

  function test_api_signatures_no_arguments() {
    $sig = $this->add_api_method(NULL, NULL);
    $this->assertArgument($sig, NULL);

    $sig = $this->add_api_method(array(), NULL);
    $this->assertArgument($sig, NULL);
  }

  function test_api_signatures_no_return_value() {
    $sig = $this->add_api_method(NULL, NULL);
    $this->assertReturnValue($sig, STUDIP_WS_TYPE_NULL);

    $sig = $this->add_api_method(NULL);
    $this->assertReturnValue($sig, STUDIP_WS_TYPE_NULL);
  }

  function test_api_signatures_base64() {
    $sig = $this->add_api_method(array('base64'), 'base64');
    $this->assertArgument($sig, STUDIP_WS_TYPE_BASE64);
    $this->assertReturnValue($sig, STUDIP_WS_TYPE_BASE64);
  }

  function test_api_signatures_boolean() {
    $sig = $this->add_api_method(array('bool', 'boolean', TRUE, FALSE), 'bool');
    $this->assertArgument($sig, STUDIP_WS_TYPE_BOOL);
    $this->assertArgument($sig, STUDIP_WS_TYPE_BOOL);
    $this->assertArgument($sig, STUDIP_WS_TYPE_BOOL);
    $this->assertArgument($sig, STUDIP_WS_TYPE_BOOL);
    $this->assertReturnValue($sig, STUDIP_WS_TYPE_BOOL);
  }

  function test_api_signatures_float() {
    $sig = $this->add_api_method(array('float', 'double', 1.0), 'float');
    $this->assertArgument($sig, STUDIP_WS_TYPE_FLOAT);
    $this->assertArgument($sig, STUDIP_WS_TYPE_FLOAT);
    $this->assertArgument($sig, STUDIP_WS_TYPE_FLOAT);
    $this->assertReturnValue($sig, STUDIP_WS_TYPE_FLOAT);
  }

  function test_api_signatures_int() {
    $sig = $this->add_api_method(array('int', 'integer', 1), 'int');
    $this->assertArgument($sig, STUDIP_WS_TYPE_INT);
    $this->assertArgument($sig, STUDIP_WS_TYPE_INT);
    $this->assertArgument($sig, STUDIP_WS_TYPE_INT);
    $this->assertReturnValue($sig, STUDIP_WS_TYPE_INT);
  }

  function test_api_signatures_string() {
    $sig = $this->add_api_method(array('string', 'text', 'helloworld42'),
                                 'string');
    $this->assertArgument($sig, STUDIP_WS_TYPE_STRING);
    $this->assertArgument($sig, STUDIP_WS_TYPE_STRING);
    $this->assertArgument($sig, STUDIP_WS_TYPE_STRING);
    $this->assertReturnValue($sig, STUDIP_WS_TYPE_STRING);
  }

  function test_api_signatures_null() {
    $sig = $this->add_api_method(array('null', NULL), 'null');
    $this->assertArgument($sig, STUDIP_WS_TYPE_NULL);
    $this->assertArgument($sig, STUDIP_WS_TYPE_NULL);
    $this->assertReturnValue($sig, STUDIP_WS_TYPE_NULL);
  }

  function test_api_signatures_array_1() {
    $arg = array();
    $sig = $this->add_api_method(array($arg));
    $this->assertError();
  }

  function test_api_signatures_array_2() {
    $arg = array(NULL);
    $sig = $this->add_api_method(array($arg));
    $this->assertError();
  }

  function test_api_signatures_array_() {
    $sig = $this->add_api_method(array(), array('int'));
    $this->assertReturnValue($sig, STUDIP_WS_TYPE_ARRAY, STUDIP_WS_TYPE_INT);

    $arg = array('int');
    $sig = $this->add_api_method(array($arg));
    $this->assertArgument($sig, STUDIP_WS_TYPE_ARRAY, STUDIP_WS_TYPE_INT);

    $arg = array(1);
    $sig = $this->add_api_method(array($arg));
    $this->assertArgument($sig, STUDIP_WS_TYPE_ARRAY, STUDIP_WS_TYPE_INT);

    $arg = array(array('int'));
    $sig = $this->add_api_method(array($arg));
    $this->assertArgument($sig, STUDIP_WS_TYPE_ARRAY, STUDIP_WS_TYPE_ARRAY, STUDIP_WS_TYPE_INT);
 }

  function test_api_signatures_struct() {
    $sig = $this->add_api_method(array('UserStruct'));
    $this->assertArgument($sig, STUDIP_WS_TYPE_STRUCT, 'UserStruct');
  }
}
