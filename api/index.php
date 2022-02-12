<?php
require __DIR__ . '/../vendor/autoload.php';

use maesierra\Japo\App\JapoApp;
use maesierra\Japo\AppContext\JapoAppContext;

(new JapoApp(JapoAppContext::context()))->run();