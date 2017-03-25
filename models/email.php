<?php

class Email extends ActiveRecord\Model {
  static $table_name = 'SpamOut';
  static $primary_key = 'spamoutid';

  static $has_many = array(
    array('email_shingles'),
    array('shingles', 'through' => 'email_shingles')
  );
}
