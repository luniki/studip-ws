<?php

/*
 * markov_chainer.php - Text generator using markov chains.
 *
 * see: http://blade.nagaokaut.ac.jp/cgi-bin/scat.rb/ruby/ruby-talk/188420
 *
 * Copyright (C) 2006 - Dominik Bathon <dbatml@gmx.de>
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class MarkovChainer {

  var $order, $beginnings, $freq;
  
  function MarkovChainer($order) {
     $this->order = $order;
     $this->beginnings = array();
     $this->freq = array();
  }
  
  function add_text($text) {
     $text = preg_replace('/\n\s*\n/m', '.', $text);
     $text .= '.';
     $seps = '/([.!?])/';
     $sentence = '';
     $text = preg_split($seps, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
     foreach ($text as $p) {
       if (preg_match($seps, $p)) {
         $this->add_sentence($sentence, $p);
         $sentence = '';
       } else {
         $sentence = $p;
       }
     }
   }

   function generate_sentence() {
     $res = $this->beginnings[rand(0, sizeof($this->beginnings) - 1)];
     while (TRUE) {
       if (!($next_word = $this->next_word_for(array_slice($res, -$this->order, $this->order)))) {
         $last = $res[sizeof($res) - 1];
         return join(' ', array_slice($res, 0, sizeof($res) - 1)) . $res[sizeof($res) - 1];
       }
       $res[] = $next_word;
     }
   }

   function add_sentence($str, $terminator) {
     $words = array();
     preg_match_all("/[\w'\"]+/", $str, $words);
     $words = $words[0];
     if (sizeof($words) <= $this->order)
       return;
     $words[] = $terminator;
     $buf = array();
     foreach ($words as $w) {
       $buf[] = $w;
       if (sizeof($buf) == ($this->order + 1)) {
         $tmp = join(' ', array_slice($buf, 0, -1));
         if (!isset($this->freq[$tmp]))
           $this->freq[$tmp] = array();
         $this->freq[$tmp][] = $buf[sizeof($buf) - 1];
         array_shift($buf);
       }
     }
     $this->beginnings[] = array_slice($words, 0, $this->order);
   }

   function next_word_for($words) {
     $joined_words = join(' ', $words);
     if (!isset($this->freq[$joined_words])) return NULL;
     $arr = $this->freq[$joined_words];
     return $arr[rand(0, sizeof($arr) - 1)];
   }
}
