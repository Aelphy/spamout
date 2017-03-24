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
            print('Nothing to save');
            return;
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
             print('Wrong path');
             
             fclose($handle_a);
             fclose($handle_b);
            
             return;
         }

         $this->a = unserialize(fread($handle_a, filesize('hash_functions_coeffs_a')));
         $this->b = unserialize(fread($handle_b, filesize('hash_functions_coeffs_b')));
         fclose($handle_a);
         fclose($handle_b);
    }

    public function sign($x) {
        if (!$this->a | !$this->b) {
            print('Generate or load functions first');
            return;
        }

        $result = array_fill(0, $this->num_functions, -1);

        for ($i = 0; $i < $this->num_functions; $i++) {
            $result[$i] = ($this->a[$i] * $x + $this->b[$i]) % $this->c; 
        }

        return $result;
    }
}

