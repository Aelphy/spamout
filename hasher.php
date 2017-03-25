<?php

class Hasher {
  var $a;
  var $b;

  function __construct($num_functions, $max_value, $c) {
    $this->num_functions = $num_functions;
    $this->max_value = $max_value;
    $this->c = $c;
  }

  public function build_hash_functions() {
    $this->a = $this->pick_random_coeffs();
    $this->b = $this->pick_random_coeffs();
  }

  public function pick_random_coeffs() {
    $result = array_fill(0, $this->num_functions, -1);

    for ($i = 0; $i < $this->num_functions; $i++) {
      $number = rand(0, $this->max_value);

      while (in_array($number, $result)) {
        $number = rand(0, $this->max_value);
      }

      $result[$i] = $number;
    }

    return $result;
  }

  public function save_hash_functions($prefix) {
    if (!$this->a | !$this->b) {
      throw new Exception('Nothing to save');
    }

    $handle_a = fopen($prefix.'_a', 'w');
    $handle_b = fopen($prefix.'_b', 'w');
    fwrite($handle_a, serialize($this->a));
    fwrite($handle_b, serialize($this->b));
    fclose($handle_a);
    fclose($handle_b);
  }

  public function load_hash_functions($prefix) {
    if ((!$handle_a = fopen($prefix.'_a', 'r')) || (!$handle_b = fopen($prefix.'_b', 'r'))) {
      fclose($handle_a);
      fclose($handle_b);

      throw new Exception('Wrong path');
    }

    $this->a = unserialize(fread($handle_a, filesize($prefix.'_a')));
    $this->b = unserialize(fread($handle_b, filesize($prefix.'_b')));
    fclose($handle_a);
    fclose($handle_b);
  }

  public function sign($x) {
    if (!$this->a | !$this->b) {
      throw new Exception('Generate or load functions first');
    }

    $result = array_fill(0, $this->num_functions, -1);

    for ($i = 0; $i < $this->num_functions; $i++) {
      $result[$i] = ($this->a[$i] * $x + $this->b[$i]) % $this->c;
    }

    return $result;
  }
}
