<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\DataExtractor\Method;

use Symfony\Component\Form\FormInterface;

/**
 * Extract data needed to apply a filter condition.
 *
 * @author <g.gauthier@lexik.com>
 * @author Gilles Gauthier <g.gauthier@lexik.fr>
 */
class DefaultExtractionMethod implements DataExtractionMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'default';
    }

    /**
     * {@inheritdoc}
     */
    public function extract(FormInterface $form)
    {
        return array('value' => $form->getData());
    }
}
