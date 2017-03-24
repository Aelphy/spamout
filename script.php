<?php

require 'vendor/autoload.php';
require 'database.php';

require 'models/email.php';
require 'models/shingle.php';
require 'models/email_shingle.php';
require 'shingler.php';
require 'min_hash.php';

$shingle_size = 3;
$shingler = new Shingler($shingle_size);
$shingler->write_shingles2db();

// build the signature matrix num_emails x num_hashes divided into b bands.
$num_hashes = 200;
$num_bands = 20;
$max_value = (1 << 32) - 1;
$prime = 4294967311; // was found to be the next after max_value

$min_hash = new MinHash($num_hashes, $max_value, $prime, $num_bands);
$min_hash->build(Email::all());

