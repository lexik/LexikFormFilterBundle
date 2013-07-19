<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\DataExtractor\Method;

use Symfony\Component\Form\FormInterface;

/**
 * Defines methods for a data extraction method class.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 * @author Gilles Gauthier <g.gauthier@lexik.fr>
 */
interface DataExtractionMethodInterface
{
    /**
     * Returns the extration method name.
     *
     * @return string
     */
    public function getName();

    /**
     * Extract data from a form.
     *
     * @param FormInterface $form
     *
     * @return array
     */
    public function extract(FormInterface $form);
}
