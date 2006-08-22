<?php

/*
 * xmlrpc_dispatcher_test.php - Testing XML-RPC Dispatcher.
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */
 
require_once 'vendor/studip_ws/studip_ws.php';
require_once 'vendor/studip_ws/xmlrpc_dispatcher.php';

require_once 'vendor/phpxmlrpc/xmlrpc.inc';


class XmlrpcDispatcherTestCase extends UnitTestCase {

  var $service, $dispatcher;

  function setUp() {
    $this->service    =& new Studip_Ws_Service(NULL);
    $this->dispatcher =& new Studip_Ws_XmlrpcDispatcher(NULL);
  }
  
  function tearDown() {
    $this->service    = NULL;
    $this->dispatcher = NULL;
  }


  function map_api_method($expects, $returns = NULL, $description = NULL) {
    return $this->dispatcher->map_service_method(
      $this->service->add_api_method(sprintf('function_in_line_%d',
                                             next(current(debug_backtrace()))),
                                     $expects,
                                     $returns,
                                     $description));
  }


  function assertArgument(&$sig, $expected_type) {

    # removing return value from front
    $return_value = array_shift($sig['signature']);

    # any arguments left?
    if (sizeof($sig['signature']) === 0) {
      if (is_null($expected_type))
        $this->pass();
      else
        $this->fail('No more arguments.');
      return;
    }
    
    # remove first argument
    $actual_type = array_shift($sig['signature']);

    $msg = sprintf('Types do not match. Actual: "%s" Expected: "%s"',
                   var_export($actual_type, TRUE),
                   var_export($expected_type, TRUE));
    $this->assertEqual($actual_type, $expected_type, $msg);

    # put return value back
    array_unshift($sig['signature'], $return_value);
  }

  function assertDescription(&$sig, $expected_description) {
    $actual_description = $sig['docstring'];
    $msg = sprintf('Descriptions do not match. Actual: "%s" Expected: "%s"',
                   var_export($actual_description, TRUE),
                   var_export($expected_description, TRUE));
    $this->assertEqual($actual_description, $expected_description);
  }
  
  function assertReturnValue(&$sig, $expected_return_value) {
    
    if (!isset($sig['signature'][0]))
      $this->fail('Missing return value.');

    $actual_return_value = $sig['signature'][0];
    $msg = sprintf('Types do not match. Actual: "%s" Expected: "%s"',
                   var_export($actual_return_value, TRUE),
                   var_export($expected_return_value, TRUE));
    $this->assertEqual($actual_return_value, $expected_return_value, $msg);
  }


  function test_map_service_description() {
    $sig = $this->map_api_method(NULL, NULL, 'foobar');
    $this->assertDescription($sig, 'foobar');
  }

  function test_map_service_no_arguments() {
    $sig = $this->map_api_method(NULL, NULL);
    $this->assertArgument($sig, NULL);

    $sig = $this->map_api_method(array(), NULL);
    $this->assertArgument($sig, NULL);
  }

  function test_map_service_returning_void() {
    $sig = $this->map_api_method(NULL, NULL);
    $this->assertReturnValue($sig, $GLOBALS['xmlrpcBoolean']);

    $sig = $this->map_api_method(NULL);
    $this->assertReturnValue($sig, $GLOBALS['xmlrpcBoolean']);
  }

  function test_map_service_base64() {
    $sig = $this->map_api_method(array('base64'), 'base64');
    $this->assertArgument($sig, $GLOBALS['xmlrpcBase64']);
    $this->assertReturnValue($sig, $GLOBALS['xmlrpcBase64']);
  }

  function test_map_service_boolean() {
    $sig = $this->map_api_method(array('bool', 'boolean', TRUE, FALSE), 'bool');
    $this->assertArgument($sig, $GLOBALS['xmlrpcBoolean']);
    $this->assertArgument($sig, $GLOBALS['xmlrpcBoolean']);
    $this->assertArgument($sig, $GLOBALS['xmlrpcBoolean']);
    $this->assertArgument($sig, $GLOBALS['xmlrpcBoolean']);
    $this->assertReturnValue($sig, $GLOBALS['xmlrpcBoolean']);
  }

  function test_map_service_float() {
    $sig = $this->map_api_method(array('float', 'double', 1.0), 'float');
    $this->assertArgument($sig, $GLOBALS['xmlrpcDouble']);
    $this->assertArgument($sig, $GLOBALS['xmlrpcDouble']);
    $this->assertArgument($sig, $GLOBALS['xmlrpcDouble']);
    $this->assertReturnValue($sig, $GLOBALS['xmlrpcDouble']);
  }

  function test_map_service_int() {
    $sig = $this->map_api_method(array('int', 'integer', 1), 'int');
    $this->assertArgument($sig, $GLOBALS['xmlrpcInt']);
    $this->assertArgument($sig, $GLOBALS['xmlrpcInt']);
    $this->assertArgument($sig, $GLOBALS['xmlrpcInt']);
    $this->assertReturnValue($sig, $GLOBALS['xmlrpcInt']);
  }

  function test_map_service_string() {
    $sig = $this->map_api_method(array('string', 'text', 'helloworld42'),
                                 'string');
    $this->assertArgument($sig, $GLOBALS['xmlrpcString']);
    $this->assertArgument($sig, $GLOBALS['xmlrpcString']);
    $this->assertArgument($sig, $GLOBALS['xmlrpcString']);
    $this->assertReturnValue($sig, $GLOBALS['xmlrpcString']);
  }

  function test_map_service_null() {
    $sig = $this->map_api_method(array('null', NULL), 'null');
    $this->assertArgument($sig, $GLOBALS['xmlrpcBoolean']);
    $this->assertArgument($sig, $GLOBALS['xmlrpcBoolean']);
    $this->assertReturnValue($sig, $GLOBALS['xmlrpcBoolean']);
  }

  function test_map_service_array() {

    $sig = $this->map_api_method(array(), array('int'));
    $this->assertReturnValue($sig, $GLOBALS['xmlrpcArray']);

    $arg = array('int');
    $sig = $this->map_api_method(array($arg));
    $this->assertArgument($sig, $GLOBALS['xmlrpcArray']);

    $arg = array(1);
    $sig = $this->map_api_method(array($arg));
    $this->assertArgument($sig, $GLOBALS['xmlrpcArray']);

    $arg = array();
    $sig = $this->map_api_method(array($arg));
    $this->assertError();

    $arg = array(NULL);
    $sig = $this->map_api_method(array($arg));
    $this->assertError();

    $arg = array(array($GLOBALS['xmlrpcInt']));
    $sig = $this->map_api_method(array($arg));
    $this->assertArgument($sig, $GLOBALS['xmlrpcArray']);
  }

  function test_map_service_struct() {
    $sig = $this->map_api_method(array('UserStruct'));
    $this->assertArgument($sig, $GLOBALS['xmlrpcStruct']);
  }
}
