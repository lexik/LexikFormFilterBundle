<?php
declare(strict_types=1);
namespace Lexik\Bundle\FormFilterBundle\Filter\Doctrine\Expression;

/**
 * Holds the value and optionally the type of an expression parameter
 * used to distinguish an array of values from the former array holding a single value and a type string
 *
 * @author Gregor Meyer https://github.com/spackmat
 */
final class ExpressionParameterValue
{
    /**
     * should be a public readonly mixed promoted property when PHP level is raised to PHP 8.1
     * @var mixed
     */
    public $value;

    /**
     * should be a public readonly ?string promoted property when PHP level is raised to PHP 8.1
     * @var ?string $type
     */
    public $type;

    public function __construct($value, ?string $type = null)
    {
        $this->value = $value;
        $this->type = $type;
    }
}
