<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Transformer;

use Symfony\Component\Form\FormInterface;

/**
 * Transform data with filter_value_key's attributes into a right format
 *
 * @author <g.gauthier@lexik.com>
 *
 */
class FilterValueKeysTransformer implements FilterTransformerInterface
{
    /**
     * {@inheritDoc}
     * @see Lexik\Bundle\FormFilterBundle\Filter\Transformer.FilterTransformerInterface::transform()
     */
    public function transform(FormInterface $form)
    {
        $data   = $form->getData();
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
