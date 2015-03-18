<?php

namespace Deblan\Validator\Constraints;

use Symfony\Component\Validator\Constraints\FileValidator;
use Symfony\Component\Validator\Constraint;
use Deblan\Csv\CsvParser;

class CsvValidator extends FileValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Csv) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Csv');
        }

        $violations = count($this->context->getViolations());

        parent::validate($value, $constraint);

        if (null === $constraint->parser) {
            throw new \RuntimeException('You must set a CSV parser.');
        }

        if (!is_object($constraint->parser) || !($value instanceof CsvParser)) {
            throw new \RuntimeException('You must be an instance of Deblan\\Csv\\CsvParser.');
        }

        if (empty($constraint->fieldsConstraints) && empty($constraint->lineConstraints)) {
            return;
        }

        $context = $this->context;
        $parser = $constraint->parser;
        $parser->setFilename($value);
        $parser->parse();

        foreach ($parser->getDatas() as $line => $data) {
            if ($parser->getHasLegend() && $line === 0) {
                continue;
            }

            foreach ($constraint->lineConstraints as $lineConstraint) {
                $violations = $this->getConstraintViolations($context, $data, $lineConstraint);

                foreach ($violations as $violation) {
                    $this->buildViolationInContext($context, $constraint->lineNotValidMessage)
                        ->setParameter('{{ line }}', $line + 1)
                        ->setParameter('{{ message }}', $violation->getMessage())
                        ->setInvalidValue(null)
                        ->setCode(Csv::LINE_NOT_VALID)
                        ->addViolation();
                }
            }

            foreach ($constraint->fieldsConstraints as $field => $fieldConstraints) {
                if (!array_key_exists($field, $line)) {
                    $this->buildViolationInContext($context, $constraint->fieldNotDetectedMessage)
                        ->setParameter('{{ field }}', ctype_digit($field) ? $field + 1 : $field)
                        ->setParameter('{{ line }}', $line + 1)
                        ->setParameter('{{ message }}', $violation->getMessage())
                        ->setInvalidValue(null)
                        ->setCode(Csv::FIELD_NOT_DETECTED)
                        ->addViolation();

                    continue;
                }

                foreach ($fieldConstraints as $fieldConstraint) {
                    $violations = $this->getConstraintViolations($context, $data[$field], $fieldConstraint);

                    foreach ($violations as $violation) {
                        $this->buildViolationInContext($context, $constraint->fieldNotValidMessage)
                            ->setParameter('{{ field }}', ctype_digit($field) ? $field + 1 : $field)
                            ->setParameter('{{ line }}', $k + 1)
                            ->setParameter('{{ message }}', $violation->getMessage())
                            ->setInvalidValue(null)
                            ->setCode(Csv::FIELD_NOT_VALID)
                            ->addViolation();
                    }
                }
            }
        }
    }

    protected function getConstraintViolations($context, $data, Constraint $constraint)
    {
        if ($context instanceof ExecutionContextInterface) {
            $violations = $context->getValidator()
                ->inContext($context)
                ->validate($data, $constraint);
        } else {
            // 2.4 API
            $violations = $context->validateValue($data, $constraint);
        }

        return $violations;
    }
}
