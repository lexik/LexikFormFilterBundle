<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\DataExtractor;

use Lexik\Bundle\FormFilterBundle\Filter\DataExtractor\Method\DataExtractionMethodInterface;
use Symfony\Component\Form\FormInterface;

/**
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class FormDataExtractor implements FormDataExtractorInterface
{
    /**
     * @var array
     */
    private $methods;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->methods = array();
    }

    /**
     * {@inheritdoc}
     */
    public function addMethod(DataExtractionMethodInterface $method)
    {
        $this->methods[$method->getName()] = $method;
    }

    /**
     * {@inheritdoc}
     */
    public function extractData(FormInterface $form, $methodName)
    {
        if (!isset($this->methods[$methodName])) {
            throw new \RuntimeException(sprintf('Unknown extration method maned "%s".', $methodName));
        }

        return $this->methods[$methodName]->extract($form);
    }
}
