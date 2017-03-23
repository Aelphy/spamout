<?php

require 'vendor/autoload.php';
require 'database.php';
require 'models/spam_out.php';
require 'shingler.php';

$shingler = new Shingler();
 
foreach (SpamOut::find('all') as $email) {
    $shingles = $shingler->transform($email->content, 5);
    break;
}

