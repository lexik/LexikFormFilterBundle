<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\DataExtractor;

use Lexik\Bundle\FormFilterBundle\Filter\DataExtractor\Method\DataExtractionMethodInterface;
use Symfony\Component\Form\FormInterface;

/**
 * @author Cédric Girard <c.girard@lexik.fr>
 */
interface FormDataExtractorInterface
{
    /**
     * Add an extration method.
     *
     * @param DataExtractionMethodInterface $method
     */
    public function addMethod(DataExtractionMethodInterface $method);

    /**
     * Extract form data by using the given method.
     *
     * @param FormInterface $form
     * @param string        $methodName
     *
     * @return array
     */
    public function extractData(FormInterface $form, $methodName);
}
