<?php

/*
 * text_generation_web_service.php - Text generation service.
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


require_once 'markov_chainer.php';


/**
 * Text generation service using markov chains.
 *
 * @package   studip
 * @package   webservice
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id$
 */
class TextGenerationWebService extends Studip_Ws_Service {

  # returns this service's api
  function get_api() {
    $api = array();

    $api['generate_text'] = array(
      'expects' => array('string', 'int'),
      'returns' => array('string'));
    
    return $api;
  }
  
  function TextGenerationWebService () {
    $this->add_api_method('generate_text',
                          array('expects' => array('string', 'int'),
                                'returns' => array('string')),
                          'Generates text using Markov chains.');
  }
  

  # first argument is api-key used to authenticate request
  function before_filter($name, &$args) {
    $api_key = current($args);
    if ($api_key !== 'secret')
      return new Studip_Ws_Fault('Could not authenticate client.');
  }


  # example filter applied to the service's result
  function after_filter($name, &$args, &$result) {
    $result = strtolower($result);
  }


  # example service operation; generates some sentences using markov chains
  function generate_text_action($api_key, $number_of_sentences) {

    $result = '';

    # create 2nd order MarkovChainer 
    $order = 2;
    $text = dirname(__FILE__).'/grimm.txt';
    $mc =& new MarkovChainer($order);
    $mc->add_text(file_get_contents($text));
    
    if ($number_of_sentences < 1) $number_of_sentences = 1;
    
    for ($i = 0; $i < $number_of_sentences; ++$i)
      $result .= $mc->generate_sentence() . "\n";

    return $result;
  }
}
