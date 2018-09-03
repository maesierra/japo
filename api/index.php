<?php
require __DIR__ . '/../vendor/autoload.php';
use maesierra\Japo\AppContext\JapoAppContext;

JapoAppContext::get()->authManager->isAuthenticated();