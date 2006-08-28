<?php

/*
 * api.php - <short-description>
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

define('STUDIP_WS_TYPE_INT',    'int');
define('STUDIP_WS_TYPE_STRING', 'string');
define('STUDIP_WS_TYPE_BASE64', 'base64');
define('STUDIP_WS_TYPE_BOOL',   'bool');
define('STUDIP_WS_TYPE_FLOAT',  'float');
define('STUDIP_WS_TYPE_ARRAY',  'array');
define('STUDIP_WS_TYPE_STRUCT', 'struct');
define('STUDIP_WS_TYPE_NULL',   'null');

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
class Studip_Ws_Type {
  
  function translate($type) {

    # complex types
    if (is_string($type) &&
        class_exists($type) &&
        Studip_Ws_Struct::is_a_struct($type))
      
      return array(STUDIP_WS_TYPE_STRUCT => $type);
    
    # array types
    if (is_array($type)) {
      if (!sizeof($type))
        trigger_error('Array of unknown type.', E_USER_ERROR);
      if (is_null($array_type = current($type)))
        trigger_error('Array of unknown type.', E_USER_ERROR);
         
      return array(STUDIP_WS_TYPE_ARRAY =>
                   Studip_Ws_Type::translate($array_type));
    }

    # basic types
    if (is_string($type))
      switch ($type) {
      
        case 'int':
        case 'integer':
                        return STUDIP_WS_TYPE_INT;

        case 'string':
        case 'text':
                        return STUDIP_WS_TYPE_STRING;

        case 'base64':
                        return STUDIP_WS_TYPE_BASE64;

        case 'bool':
        case 'boolean':
                        return STUDIP_WS_TYPE_BOOL;

        case 'float':
        case 'double':
                        return STUDIP_WS_TYPE_FLOAT;

        case 'null':
                        return STUDIP_WS_TYPE_NULL;
      }

    # type by example
    $type_checkers = array(
      'is_bool'   => STUDIP_WS_TYPE_BOOL,
      'is_float'  => STUDIP_WS_TYPE_FLOAT,
      'is_int'    => STUDIP_WS_TYPE_INT,
      'is_string' => STUDIP_WS_TYPE_STRING,
      'is_null'   => STUDIP_WS_TYPE_NULL,
      );
    foreach ($type_checkers as $function => $replacement)
      if ($function($type))
        return $replacement;
    
    trigger_error('"' . var_export($type, TRUE) . '" is not a valid type.');
  }
}
