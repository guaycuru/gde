<?php
// cli-config.php

use \Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once("common/doctrine.inc.php");

return ConsoleRunner::createHelperSet($_EM);