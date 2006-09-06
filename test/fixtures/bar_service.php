<?php

/*
 * bar_service.php - Testing Services
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


class BarService extends Studip_Ws_Service {

  function BarService() {
    $this->add_api_method('test',                      array(), 'bool');
    $this->add_api_method('test_fault',                array(), 'null');

    $this->add_api_method('test_before_false',         array(), NULL);
    $this->add_api_method('test_before_fault',         array(), NULL);

    $this->add_api_method('test_before_change_action', array('string'), 'string');
    $this->add_api_method('test_before_change_arg',    array('string'), 'string');

    $this->add_api_method('test_after_change_result',  array(), 'string');
    $this->add_api_method('test_after_fault',          array(), NULL);

    $this->add_api_method('echo', array('string'), 'string');
  }


  function before_filter(&$name, &$args) {

    switch ($name) {

    	case 'test_before_false': 
    	
    	  return FALSE;


    	case 'test_before_fault':
    	  
    	  return new Studip_Ws_Fault($name);


    	case 'test_before_change_action':
  
        $name = 'echo';	  
    	  break;


    	case 'test_before_change_arg':
  
        $args = array('arg changed');
    	  break;
    }
  }

  function after_filter(&$name, &$args, &$result) {

    switch ($name) {

    	case 'test_after_fault':

        $result =& new Studip_Ws_Fault($name);
        break;

    	case 'test_after_change_result':

        $result = 'result changed';
        break;

    }
  }


  function test_action() {
    return TRUE;
  }

  function test_fault_action() {
    return new Studip_Ws_Fault('test_fault');
  }


  function test_before_false_action() {
    trigger_error('will never be called', E_USER_ERROR);
  }

  function test_before_fault_action() {
    trigger_error('will never be called', E_USER_ERROR);
  }


  function test_before_change_action_action() {
    trigger_error('will never be called', E_USER_ERROR);
  }

  function test_before_change_arg_action($string) {
    return $this->echo_action($string);
  }

  
  function test_after_change_result_action() {
    return new Studip_Ws_Fault('This fault will never be delivered.');
  }

  function test_after_fault_action() {
  }
  

  function echo_action($string) {
    return $string;
  }
}
