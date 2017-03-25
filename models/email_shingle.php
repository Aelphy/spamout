<?php

class EmailShingle extends ActiveRecord\Model {
  static $belongs_to = array(
    array('email'),
    array('shingle')
  );
}
