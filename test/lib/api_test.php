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
 
require_once 'vendor/studip_ws/api.php';
require_once 'vendor/studip_ws/service.php';
require_once 'vendor/studip_ws/struct.php';

/**
 * Test class for Studip_Ws_Structs.
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id$
 */
class UserStruct extends Studip_Ws_Struct {
  function init() {
    UserStruct::add_member('name', 'string');
    UserStruct::add_member('id',   1);
    UserStruct::add_member('next', 'string');
    var_dump(UserStruct::members());
    
  }
}

class ApiTestCase extends UnitTestCase {

  var $service;

  function setUp() {
    $this->service =& new Studip_Ws_Service();
  }
  
  function tearDown() {
  }



  function add_api_method($name, $expects, $returns = NULL, $description = '') {

    $name = sprintf('function_in_line_', $name);

    $options = array();
    if (!is_null($expects)) $options['expects'] = $expects;
    if (sizeof(func_get_args()) > 2) $options['returns'] = $returns;

    $this->service->add_api_method($name, $options, $description);
    return $this->service->api_methods[$name];
  }



  function assertArgument(&$sig, $expected_type) {
    $actual_type = array_shift($sig['expects']);
    $msg = sprintf('Types do not match. Actual: "%s" Expected: "%s"',
                   var_export($actual_type, TRUE),
                   var_export($expected_type, TRUE));
    $this->assertEqual($actual_type, $expected_type, $msg);
  }
  
  function assertDescription(&$sig, $expected_description) {
    $actual_description = $sig['description'];
    $msg = sprintf('Descriptions do not match. Actual: "%s" Expected: "%s"',
                   var_export($actual_description, TRUE),
                   var_export($expected_description, TRUE));
    $this->assertEqual($actual_description, $expected_description);
  }
  
  function assertReturnValue(&$sig, $expected_return_value) {
    $actual_return_value = $sig['returns'];
    $msg = sprintf('Types do not match. Actual: "%s" Expected: "%s"',
                   var_export($actual_return_value, TRUE),
                   var_export($expected_return_value, TRUE));
    $this->assertEqual($actual_return_value, $expected_return_value, $msg);
  }



  function test_api_signatures_description() {
    $sig = $this->add_api_method(__LINE__, NULL, NULL, 'foobar');
    $this->assertDescription($sig, 'foobar');
  }

  function test_api_signatures_no_arguments() {
    $sig = $this->add_api_method(__LINE__, NULL, NULL);
    $this->assertArgument($sig, NULL);

    $sig = $this->add_api_method(__LINE__, array(), NULL);
    $this->assertArgument($sig, NULL);
  }

  function test_api_signatures_no_return_value() {
    $sig = $this->add_api_method(__LINE__, NULL, NULL);
    $this->assertReturnValue($sig, 'null');

    $sig = $this->add_api_method(__LINE__, NULL);
    $this->assertReturnValue($sig, 'null');
  }

  function test_api_signatures_base64() {
    $sig = $this->add_api_method(__LINE__,
                                 array('base64'),
                                 'base64');
    $this->assertArgument($sig, 'base64');
    $this->assertReturnValue($sig, 'base64');
  }

  function test_api_signatures_boolean() {
    $sig = $this->add_api_method(__LINE__,
                                 array('bool', 'boolean', TRUE, FALSE),
                                 'bool');
    $this->assertArgument($sig, 'bool');
    $this->assertArgument($sig, 'bool');
    $this->assertArgument($sig, 'bool');
    $this->assertArgument($sig, 'bool');
    $this->assertReturnValue($sig, 'bool');
  }

  function test_api_signatures_float() {
    $sig = $this->add_api_method(__LINE__,
                                 array('float', 'double', 1.0),
                                 'float');
    $this->assertArgument($sig, 'float');
    $this->assertArgument($sig, 'float');
    $this->assertArgument($sig, 'float');
    $this->assertReturnValue($sig, 'float');
  }

  function test_api_signatures_int() {
    $sig = $this->add_api_method(__LINE__,
                                 array('int', 'integer', 1),
                                 'int');
    $this->assertArgument($sig, 'int');
    $this->assertArgument($sig, 'int');
    $this->assertArgument($sig, 'int');
    $this->assertReturnValue($sig, 'int');
  }

  function test_api_signatures_string() {
    $sig = $this->add_api_method(__LINE__,
                                 array('string', 'text', 'helloworld'),
                                 'string');
    $this->assertArgument($sig, 'string');
    $this->assertArgument($sig, 'string');
    $this->assertArgument($sig, 'string');
    $this->assertReturnValue($sig, 'string');
  }


  function test_api_signatures_array() {

    $sig = $this->add_api_method(__LINE__, array(), array('int'));
    $this->assertReturnValue($sig, array('int'));

    $arg = array('int');
    $exp = array('int');
    $sig = $this->add_api_method(__LINE__, array($arg));
    $this->assertArgument($sig, $exp);

    $arg = array(1);
    $exp = array('int');
    $sig = $this->add_api_method(__LINE__, array($arg));
    $this->assertArgument($sig, $exp);

    $arg = array();
    $sig = $this->add_api_method(__LINE__, array($arg));
    $this->assertError();

    $arg = array(NULL);
    $sig = $this->add_api_method(__LINE__, array($arg));
    $this->assertError();

    $arg = array(array('int'));
    $exp = array(array('int'));
    $sig = $this->add_api_method(__LINE__, array($arg));
    $this->assertArgument($sig, $exp);
  }


  function test_api_signatures_struct() {
    $sig = $this->add_api_method(__LINE__, array('UserStruct'));
    $this->assertArgument($sig, 'struct UserStruct {string:name; int:id; string:next;};');
  }
}
