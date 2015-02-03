<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\DataExtractor\Method;

use Symfony\Component\Form\FormInterface;

/**
 * Extract data needed to apply a filter condition.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 * @author Gilles Gauthier <g.gauthier@lexik.fr>
 */
class ValueKeysExtractionMethod implements DataExtractionMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'value_keys';
    }

    /**
     * {@inheritdoc}
     */
    public function extract(FormInterface $form)
    {
        $data   = $form->getData() ?: array();
        $keys   = array();
        $config = $form->getConfig();

        if ($config->hasAttribute('filter_value_keys')) {
            $keys = array_merge($data, $config->getAttribute('filter_value_keys'));
        }

        $values = array('value' => array());

        foreach ($keys as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values['value'][$key][] = $data[$key];

                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $values['value'][$key][$k] = $v;
                    }
                }
            } else {
                throw new \InvalidArgumentException(sprintf('No value found for key "%s" in form data.', $key));
            }
        }

        return $values;
    }
}
