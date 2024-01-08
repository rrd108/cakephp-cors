<?php

use Cake\Core\Configure;

Configure::load('Cors.cors');
try {
    Configure::load('cors');
} catch (Exception $exception) {
    //debug($exception->getMessage());
}
