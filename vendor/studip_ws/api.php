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
class Studip_Ws_Api {
  
  function translate_signature_entry($type) {
    
    # array
    if (is_array($type)) {
      if (!sizeof($type))
        trigger_error('Array of unknown type.', E_USER_ERROR);
      if (is_null($array_type = current($type)))
        trigger_error('Array of unknown type.', E_USER_ERROR);
         
      return array(Studip_Ws_Api::translate_signature_entry($array_type));
    }

    # literal type
    if (is_string($type))
      switch ($type) {
      
        case 'int':
        case 'integer':
                        return 'int';

        case 'string':
        case 'text':
                        return 'string';

        case 'base64':
                        return 'base64';

        case 'bool':
        case 'boolean':
                        return 'bool';

        case 'float':
        case 'double':
                        return 'float';
      }

    # structs
#     static $structs;
#     if (is_null($structs))
#       $structs = array();

    if (class_exists($type) && Studip_Ws_Struct::is_a_struct($type)) {
#       $members = array();
#       UserStruct::init();
#       var_dump(Studip@@@TODO@@@::members());

#       if (!isset($structs[strtolower($type)])) {
#         $structs[strtolower($type)] = TRUE;
#         var_dump($structs);
#         
#       }
        $type_instance =& new $type();
        var_dump($type_instance->get_elements());
      
      
      return "hallo";

      foreach (call_user_func(array($type,'members')) as $n => $t)
        if ($t)
          $members[] = sprintf('%s:%s;',
                               Studip_Ws_Api::translate_signature_entry($t['type']),
                               $n);
      return sprintf('struct %s {%s};', $type, implode(' ', $members));
    }

    # by example
    $type_checkers = array(
      'is_bool'   => 'bool',
      'is_float'  => 'float',
      'is_int'    => 'int',
      'is_string' => 'string',
      'is_null'   => 'null',
      # 'is_object' => '???',
      );
    foreach ($type_checkers as $function => $replacement)
      if ($function($type))
        return $replacement;
    
    trigger_error('"' . var_export($type, TRUE) . '" is not a valid type.');
  }
}
