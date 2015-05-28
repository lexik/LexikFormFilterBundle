<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\DataExtractor;

use Symfony\Component\Form\FormInterface;
use Lexik\Bundle\FormFilterBundle\Filter\DataExtractor\Method\DataExtractionMethodInterface;

/**
 *
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
     * @return array
     */
    public function extractData(FormInterface $form, $methodName);
}
