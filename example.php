<?php

use Deblan\Csv\CsvParser;
use Deblan\CsvValidator\Validator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Date;

chdir(__DIR__);

require 'vendor/autoload.php';

$parser = new CsvParser('lqdn.txt', ';', '');

$validator = new Validator($parser, Validation::createValidator());
$validator->addFieldConstraint(1, new Email());
$validator->validate();

var_dump($validator->isValid());
var_dump($validator->getErrors());
