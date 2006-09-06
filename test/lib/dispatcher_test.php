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
require_once 'test/fixtures/bar_service.php';


class DispatcherTestCase extends UnitTestCase {

  var $dispatcher;

  function setUp() {
    $this->dispatcher =& new Studip_Ws_Dispatcher('BarService');
  }
  
  function tearDown() {
    $this->dispatcher = NULL;
  }

  #
  function test_missing_arguments() {

    new Studip_Ws_Dispatcher();
    $this->assertNoErrors();

    $this->expectError(new PatternExpectation('/Missing argument 1/'));
    $this->expectError(new PatternExpectation('/Arguments must be strings./'));
    $this->dispatcher->add_service();


    new Studip_Ws_Dispatcher(array());
    $this->assertNoErrors();

    $this->expectError(new PatternExpectation('/Arguments must be strings./'));
    $this->dispatcher->add_service(array());
  }

  #
  function test_wrong_arguments() {

    $does_not_exist =& new PatternExpectation('/does not exist/');


    $this->expectError($does_not_exist);
    new Studip_Ws_Dispatcher('foo');

    $this->expectError($does_not_exist);
    $this->dispatcher->add_service('foo');


    $this->expectError($does_not_exist);
    new Studip_Ws_Dispatcher(__CLASS__);

    $this->expectError($does_not_exist);
    $this->dispatcher->add_service(__CLASS__);


    $arguments_must_be_strings =&
      new PatternExpectation('/Arguments must be strings/');


    $this->expectError($arguments_must_be_strings);
    new Studip_Ws_Dispatcher($this);

    $this->expectError($arguments_must_be_strings);
    $this->dispatcher->add_service($this);


    $this->expectError($arguments_must_be_strings);
    new Studip_Ws_Dispatcher(new Studip_Ws_Service());

    $this->expectError($arguments_must_be_strings);
    $this->dispatcher->add_service(new Studip_Ws_Service());


    $this->expectError($arguments_must_be_strings);
    new Studip_Ws_Dispatcher(new FooService());

    $this->expectError($arguments_must_be_strings);
    $this->dispatcher->add_service(new FooService());
  }

  #
  function test_right_arguments() {

    new Studip_Ws_Dispatcher('Studip_Ws_Service');
    $this->dispatcher->add_service('Studip_Ws_Service');
    $this->assertNoErrors();

    new Studip_Ws_Dispatcher('FooService');
    $this->dispatcher->add_service('FooService');
    $this->assertNoErrors();

    new Studip_Ws_Dispatcher('Studip_Ws_Service', 'FooService');
    $this->assertNoErrors();

    new Studip_Ws_Dispatcher(array('Studip_Ws_Service', 'FooService'));
    $this->assertNoErrors();

    new Studip_Ws_Dispatcher('BarService');
    $this->assertNoErrors();
  }

  #
  function test_duplicate_methods() {

    $this->expectError(new PatternExpectation('/Method test already defined/'));
    new Studip_Ws_Dispatcher('BarService', 'BarService');

    $this->expectError(new PatternExpectation('/Method test already defined/'));
    $this->dispatcher->add_service('BarService');
  }

  #
  function test_responds_to() {

    $responds = $this->dispatcher->responds_to('test');
    $this->assertTrue($responds);

    $does_not_respond = $this->dispatcher->responds_to('foobar');
    $this->assertFalse($does_not_respond);

    $this->dispatcher->responds_to(NULL);
    $this->assertNoErrors();

    $this->dispatcher->responds_to(1);
    $this->assertNoErrors();

    $this->expectError(new PatternExpectation('/Object to string/'));
    $this->dispatcher->responds_to($this);
  }
  
  #
  function test_before_filter() {

    $this->expectError(new PatternExpectation('/before_filter activated/'));
    $this->dispatcher->invoke('test_before_false', array());
    
    $this->expectError(new PatternExpectation('/test_before_fault/'));
    $this->dispatcher->invoke('test_before_fault', array());

    $echoed = $this->dispatcher->invoke('test_before_change_action',
                                        array('this arg gets echoed'));
    $this->assertEqual($echoed, 'this arg gets echoed');

    $echoed = $this->dispatcher->invoke('test_before_change_arg',
                                        array('this arg will change'));
    $this->assertEqual($echoed, 'arg changed');
  }

  #
  function test_action() {

    $result = $this->dispatcher->invoke('test', array());
    $this->assertTrue($result);
    $this->assertNoErrors();

    $this->expectError(new PatternExpectation('/test_fault/'));
    $result = $this->dispatcher->invoke('test_fault', array());
  }

  #
  function test_after_filter() {
    $echoed = $this->dispatcher->invoke('test_after_change_result',
                                        array());
    $this->assertEqual($echoed, 'result changed');

    $this->expectError(new PatternExpectation('/test_after_fault/'));
    $result = $this->dispatcher->invoke('test_after_fault', array());
  }
}
