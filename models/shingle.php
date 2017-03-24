<?php

class Shingle extends ActiveRecord\Model {
    static $has_many = array(
        array('email_shingles'),
        array('emails', 'through' => 'email_shingles')
    );
    
    static $validates_presence_of = array(
        array('content', 'message' => 'cannot be blank')
    );
}

