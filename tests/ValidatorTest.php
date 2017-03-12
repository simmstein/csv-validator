<?php

use Deblan\Csv\CsvParser;
use Deblan\CsvValidator\Validator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testViolation()
    {
        $parser = $this->generateParser('example.csv');
        $validator = $this->generateValidator($parser);
        $this->setExpectedException('\RuntimeException');
        $validator->isValid();
    }

    public function testExpectedHeaders()
    {
        $parser = $this->generateParser('example.csv');
        $parser->setHasHeaders(true);

        $validator = $this->generateValidator();
        $validator->setExpectedHeaders(['foo', 'bar', 'boo']);
        $validator->validate($parser->parseFile(__DIR__.'/fixtures/example.csv'));
        $this->assertEquals(true, $validator->isValid());
        $this->assertEquals(0, count($validator->getErrors()));

        $validator = $this->generateValidator();
        $validator->setExpectedHeaders(['bad', 'legend']);
        $validator->validate($parser->parseFile(__DIR__.'/fixtures/example.csv'));
        $this->assertEquals(false, $validator->isValid());
        $this->assertEquals(1, count($validator->getErrors()));
    }

    public function testNoConstraint()
    {
        $parser = $this->generateParser('example.csv');
        $validator = $this->generateValidator();
        $validator->validate($parser->parseFile(__DIR__.'/fixtures/example.csv'));
        $this->assertEquals(true, $validator->isValid());
    }

    public function testFieldContraintsOk()
    {
        $parser = $this->generateParser('example.csv');
        $validator = $this->generateValidator();
        $validator->addFieldConstraint(0, new NotBlank());
        $validator->validate($parser->parseFile(__DIR__.'/fixtures/example.csv'));
        $this->assertEquals(true, $validator->isValid());
        $this->assertEquals(0, count($validator->getErrors()));

        $parser = $this->generateParser('example.csv');
        $validator = $this->generateValidator();
        $validator->addFieldConstraint(1, new NotBlank());
        $validator->validate($parser->parseFile(__DIR__.'/fixtures/example.csv'));
        $this->assertEquals(true, $validator->isValid());
        $this->assertEquals(0, count($validator->getErrors()));

        $parser = $this->generateParser('example.csv');
        $validator = $this->generateValidator();
        $validator->addFieldConstraint(0, new NotBlank());
        $validator->addFieldConstraint(1, new NotBlank());
        $validator->validate($parser->parseFile(__DIR__.'/fixtures/example.csv'));
        $this->assertEquals(true, $validator->isValid());
        $this->assertEquals(0, count($validator->getErrors()));
    }

    public function testFieldContraintsKo()
    {
        $parser = $this->generateParser();
        $validator = $this->generateValidator();
        $validator->addFieldConstraint(0, new Email());
        $validator->validate($parser->parseFile(__DIR__.'/fixtures/example.csv'));
        $this->assertEquals(false, $validator->isValid());
        $this->assertEquals(2, count($validator->getErrors()));

        $parser = $this->generateParser();
        $validator = $this->generateValidator();
        $validator->addFieldConstraint(1, new Email());
        $validator->validate($parser->parseFile(__DIR__.'/fixtures/example.csv'));
        $this->assertEquals(false, $validator->isValid());
        $this->assertEquals(5, count($validator->getErrors()));

        $parser = $this->generateParser('example.csv');
        $validator = $this->generateValidator();
        $validator->addFieldConstraint(0, new Email());
        $validator->addFieldConstraint(1, new Email());
        $validator->validate($parser->parseFile(__DIR__.'/fixtures/example.csv'));
        $this->assertEquals(false, $validator->isValid());
        $this->assertEquals(7, count($validator->getErrors()));
    }

    protected function generateParser()
    {
        return new CsvParser();
    }

    protected function generateValidator()
    {
        return new Validator(Validation::createValidator());
    }
}
