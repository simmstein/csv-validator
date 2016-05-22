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
    
    public function testNoConstraint()
    {
        $parser = $this->generateParser('example.csv');
        $validator = $this->generateValidator($parser);
        $validator->validate();
        $this->assertEquals(true, $validator->isValid());
    }
    
    public function testFieldContraintsOk()
    {
        $parser = $this->generateParser('example.csv');
        $validator = $this->generateValidator($parser);
        $validator->addFieldConstraint(0, new NotBlank());
        $validator->validate();
        $this->assertEquals(true, $validator->isValid());
        $this->assertEquals(0, count($validator->getErrors()));
        
        $parser = $this->generateParser('example.csv');
        $validator = $this->generateValidator($parser);
        $validator->addFieldConstraint(1, new NotBlank());
        $validator->validate();
        $this->assertEquals(true, $validator->isValid());
        $this->assertEquals(0, count($validator->getErrors()));
        
        $parser = $this->generateParser('example.csv');
        $validator = $this->generateValidator($parser);
        $validator->addFieldConstraint(0, new NotBlank());
        $validator->addFieldConstraint(1, new NotBlank());
        $validator->validate();
        $this->assertEquals(true, $validator->isValid());
        $this->assertEquals(0, count($validator->getErrors()));
    }
    
    public function testFieldContraintsKo()
    {
        $parser = $this->generateParser('example.csv');
        $validator = $this->generateValidator($parser);
        $validator->addFieldConstraint(0, new Email());
        $validator->validate();
        $this->assertEquals(false, $validator->isValid());
        $this->assertEquals(4, count($validator->getErrors()));
        
        $parser = $this->generateParser('example.csv');
        $validator = $this->generateValidator($parser);
        $validator->addFieldConstraint(1, new Email());
        $validator->validate();
        $this->assertEquals(false, $validator->isValid());
        $this->assertEquals(4, count($validator->getErrors()));
        
        $parser = $this->generateParser('example.csv');
        $validator = $this->generateValidator($parser);
        $validator->addFieldConstraint(0, new Email());
        $validator->addFieldConstraint(1, new Email());
        $validator->validate();
        $this->assertEquals(false, $validator->isValid());
        $this->assertEquals(8, count($validator->getErrors()));
    }

    protected function generateParser($file)
    {
        return new CsvParser(__DIR__.'/fixtures/'.$file);
    }

    protected function generateValidator(CsvParser $parser) 
    {
        return new Validator($parser, Validation::createValidator());
    }
}
