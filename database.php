<?php

ActiveRecord\Config::initialize(function($cfg) {
  $cfg->set_model_directory('models');
  $cfg->set_connections(
    array('development' => 'mysql://proton:mail@localhost/spam_out_dev')
  );
});
