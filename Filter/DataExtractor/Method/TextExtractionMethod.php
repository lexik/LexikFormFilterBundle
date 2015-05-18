<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\DataExtractor\Method;

use Symfony\Component\Form\FormInterface;

/**
 * Extract data needed to apply a filter condition.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 * @author Gilles Gauthier <g.gauthier@lexik.fr>
 */
class TextExtractionMethod implements DataExtractionMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function extract(FormInterface $form)
    {
        $data   = $form->getData();
        $values = array('value' => array());

        if (array_key_exists('text', $data)) {
            $values = array('value' => $data['text']);
            $values += $data;
        }

        return $values;
    }
}
