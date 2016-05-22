<?php

namespace Deblan\CsvValidator;

use Deblan\Csv\CsvParser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class Validator
 * @author Simon Vieille <simon@deblan.fr>
 */
class Validator
{
    /**
     * @var CsvParser
     */
    protected $parser;

    /**
     * @var RecursiveValidator
     */
    protected $validator;

    /**
     * @var array
     */
    protected $fieldConstraints = [];

    /**
     * @var array
     */
    protected $dataConstraints = [];

    /**
     * @var boolean
     */
    protected $hasValidate = false;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * Constructor
     *
     * @param CsvParser $parser
     * @param RecursiveValidator $validator
     */
    public function __construct(CsvParser $parser, RecursiveValidator $validator)
    {
        $this->parser = $parser;
        $this->parser->parse();
        $this->validator = $validator;
    }

    /**
     * Append a constraint to a specific column
     *
     * @param $key The column number
     * @param Constraint $constraint The constraint
     * @return Validator
     */
    public function addFieldConstraint($key, Constraint $constraint)
    {
        if (!array_key_exists($key, $this->fieldConstraints)) {
            $this->fieldConstraints[$key] = [];
        }

        $this->fieldConstraints[$key][] = $constraint;

        return $this;
    }

    /**
     * Append a constraint to a specific line
     *
     * @param $key The column number
     * @param Constraint $constraint The constraint
     * @return Validator
     */
    public function addDataConstraint(Constraint $constraint)
    {
        $this->dataConstraints[] = $constraint;

        return $this;
    }

    /**
     * Set the excepted legend
     *
     * @param array $legend Expected legend
     * @return Validator
     */
    public function setExceptedLegend(array $legend) 
    {
        $this->expectedLegend = $legend;

        return $this;
    }

    /**
     * Run the validation
     */
    public function validate()
    {
        if ($this->hasValidate) {
            return;
        }

        $this->validateLegend();
        $this->validateDatas();
        $this->validateFields();


        $this->hasValidate = true;
    }
    
    protected function validateLegend() 
    {
        if (!$this->parser->getHasLegend()) {
            return;
        }

        if (null === $this->expectedLegend) {
            return;
        }
    
        if ($this->parser->getLegend() !== $this->expectedLegend) {
            $this->mergeErrorMessage('Invalid legend.', 1);
        }
    }

    /**
     * Validates datas
     */
    protected function validateDatas() 
    {
        if (empty($this->dataConstraints)) {
            return;
        }

        foreach ($this->parser->getDatas() as $line => $data) {
            foreach ($this->dataConstraints as $constraint) {
                $violations = $this->validator->validateValue($data, $constraint);

                $this->mergeViolationsMessages($violations, $this->getTrueLine($line));
            }
        }
    }

    /**
     * Validates fields
     */
    protected function validateFields() 
    {
        if (empty($this->fieldConstraints)) {
            return;
        }
        
        foreach ($this->parser->getDatas() as $line => $data) {
            foreach ($this->fieldConstraints as $key => $constraints) {
                if (!isset($data[$key])) {
                    $column = $this->getTrueColunm($key);
                    $this->mergeErrorMessage(
                        sprintf('Field "%s" does not exist.', $column), 
                        $this->getTrueLine($line), 
                        $column
                    );
                } else {
                    foreach ($constraints as $constraint) {
                        $violations = $this->validator->validateValue($data[$key], $constraint);

                        $this->mergeViolationsMessages(
                            $violations, 
                            $this->getTrueLine($line), 
                            $this->getTrueColunm($key)
                        );
                    }
                }
            }
        }
    }

    /**
     * Add violations
     *
     * @param ConstraintViolationList $violations
     * @param integer $line The line of the violations
     * @param integer|null $key The column of the violations
     */
    protected function mergeViolationsMessages(ConstraintViolationList $violations, $line, $key = null)
    {
        if (count($violations) === 0) {
            return;
        }

        foreach ($violations as $violation) {
            $this->errors[] = $this->generateViolation($line, $key, $violation);
        }
    }

    /**
     * Create and append a violation from a string error
     *
     * @param string $message The error message
     * @param integer $line The line of the violations
     * @param integer|null $key The column of the violations
     */
    protected function mergeErrorMessage($message, $line, $key = null)
    {
        $violation = $this->generateConstraintViolation($message);
        $this->errors[] = $this->generateViolation($line, $key, $violation);
    }

    /**
     * Returns the validation status
     *
     * @return boolean
     * @throw RuntimeException No validation yet
     */
    public function isValid()
    {
        if (!$this->hasValidate) {
            throw new \RuntimeException('You must validate before.');
        }

        return empty($this->errors);
    }

    /**
     * Returns the errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Generate a ConstraintViolation
     *
     * @param string $message The error message
     * @return ConstraintViolation
     */
    protected function generateConstraintViolation($message) 
    {
        return new ConstraintViolation($message, $message, [], null, '', null);
    }
    
    /**
     * Generate a Violation
     *
     * @param string $message The error message
     * @param integer $line The line of the violations
     * @param integer|null $key The column of the violations
     * @return Violation
     */
    protected function generateViolation($line, $key, ConstraintViolation $violation) 
    {
        return new Violation($line, $key, $violation);
    }

    /**
     * Get the true line number of an error
     *
     * @param integer $line
     * @return integer
     */
    protected function getTrueLine($line) 
    {
        if ($this->parser->getHasLegend()) {
            $line++;
        }

        return ++$line;
    }
    
    /**
     * Get the true culumn number of an error
     *
     * @param integer $key
     * @return integer
     */
    protected function getTrueColunm($key) 
    {
        return ++$key;
    }
}
