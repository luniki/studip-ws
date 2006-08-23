<?php

/*
 * soap_dispatcher.php - Delegate for Stud.IP SOAP Server.
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * <ClassDescription>
 *
 * @package   studip
 * @package   ws
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id$
 */

class Studip_Ws_SoapDispatcher extends Studip_Ws_Dispatcher
                            /* implements SoapServerDelegate */ {


  /**
   * <MethodDescription>
   *
   * @param mixed <description>
   *
   * @return bool <description>
   */
  function register_services(&$server) {

    $namespace = $server->wsdl->schemaTargetNamespace;

    foreach ($this->api_methods as $method_name => $method) {

      # return value
      $method->returns = array('returns' =>
        $this->translate_type($server, $method->returns));

      # arguments
      $expects = array();
      foreach ($method->expects as $key => $type)
        $expects['param'.$key] = $this->translate_type($server, $type);
      $method->expects = $expects;

      $server->register($method->name,
                        #array('api_key'             => 'xsd:string',
                        #      'number_of_sentences' => 'xsd:int'),
                        $method->expects,
                        #array('return'  => 'xsd:string'),
                        $method->returns,
                        $namespace,
                        $namespace . '#' . $method->name,
                        'rpc',
                        'encoded',
                        $method->description);
    }
    
    return TRUE;
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function throw_exception($message/*, ...*/) {
    $args = func_get_args();
    return new soap_fault('Client', '', vsprintf(array_shift($args), $args));
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   * @param type <description>
   *
   * @return type <description>
   */
  function translate_type($server, $type) {

    # primitive types
    if (is_string($type))

      switch ($type) {
        case STUDIP_WS_TYPE_INT:
                                   return 'xsd:int';

        case STUDIP_WS_TYPE_STRING:
                                   return 'xsd:string';

        case STUDIP_WS_TYPE_BASE64:
                                   return 'xsd:base64';

        case STUDIP_WS_TYPE_BOOL:
                                   return 'xsd:boolean';

        case STUDIP_WS_TYPE_FLOAT:
                                   return 'xsd:double';

        case STUDIP_WS_TYPE_NULL:
                                   return 'xsd:boolean';
      }
    
    # complex types
    if (is_array($type)) {

var_dump($server->wsdl->complexTypes);
    
      # is an array
      if (($key = key($type)) === STUDIP_WS_TYPE_ARRAY) {
        
        list($element_ns, $element_type) =
          explode(':', $this->translate_type($server, current($type)));
        
        $server->wsdl->addComplexType(
          $element_type . 'Array',
          'complexType', 'array', '', 'SOAP-ENC:Array', array(),
          array(
            array('ref' => 'SOAP-ENC:arrayType',
                  'wsdl:arrayType' => 'tns:'.$element_type.'[]')),
          $element_ns.':'.$element_type
        );
      
        return 'tns:' . $element_type . 'Array';      
      }
      
      # is a struct
      if ($key === STUDIP_WS_TYPE_STRUCT) {



        $struct_type = current($type);
        $this->symbols[strtolower($struct_type)] = TRUE;
        # var_dump('set symbol: ' . $struct_type);
        
        $struct =& new $struct_type();

        $struct_elements = array();
        foreach ($struct->get_elements() as $element) {

# var_dump('asking for '.current($element->type));
if (isset($this->symbols[strtolower(current($element->type))])) {
  # var_dump('found ' . current($element->type));
  return 'tns:' . current($element->type);
}

          $struct_elements[$element->name] = array(
            'name' => $element->name,
            'type' => $this->translate_type($server, $element->type));
        }
        
        $server->wsdl->addComplexType($struct_type,
          'complexType', 'struct', 'all', '', $struct_elements);
        
        return 'tns:' . $struct_type;
      }
    }      

    trigger_error(sprintf('Type %s could not be found.', 
                          var_export($type, TRUE)),
                  E_USER_ERROR);

#    return 'tns:' . $type;
  }
}
