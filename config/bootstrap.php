<?php

use Cake\Core\Configure;

Configure::load('Rrd108/Cors.cors');
try {
    Configure::load('cors');
} catch (Exception $exception) {
    //debug($exception->getMessage());
}
