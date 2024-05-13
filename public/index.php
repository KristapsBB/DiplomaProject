<?php

error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

DiplomaProject\Core\Core::getCurrentApp()->run('default');
