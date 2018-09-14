<?php

require __DIR__ . '/../vendor/autoload.php';
use \maesierra\Japo\AppContext\JapoAppContext;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

return ConsoleRunner::createHelperSet(JapoAppContext::context()->entityManager);
