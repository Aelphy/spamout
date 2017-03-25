<?php

require 'vendor/autoload.php';
require 'database.php';

require 'models/email.php';
require 'models/shingle.php';
require 'models/email_shingle.php';
require 'min_hash.php';

$shingle_size = 3;
$shingler = new Shingler($shingle_size);
$shingler->write_email_shingles2db();

// build the signature matrix num_emails x num_hashes divided into b bands.
$num_hashes = 200;
$num_bands = 20;
$max_value = (1 << 32) - 1;
$prime = 4294967311; // was found to be the next after max_value

$min_hash = new MinHash($num_hashes, $max_value, $prime, $num_bands);
$min_hash->build(Email::all());

// Query document with threshold on Jaccard similarity

$thr = 0.8;
$email = Email::first();
$nearest_neighbours = $min_hash->query($email->content.' '.$email->metadata, $thr, $shingle_size);

if ($nearest_neighbours) {
    $spam_count = 0;

    // here one could want to limit the amount of neighbours with some contant value of K 
    foreach ($nearest_neighbours as $neighbour) {
        // Check spam mark
        if (Email::find($neighbour)->type == 1) {
            $spam_count++;
        }
    }

    // NOTE: One can also account the similarity to spam neighbours and introduce a weighted vonting
    if ($spam_count > count($nearest_neighbours) / 2) {
        print('according to the cotings the text is not unique');
    } else {
        print('there were not enough spam in the neighbourhood');
    }
} else {
    print('the text is unique');
}

