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
        $data = $form->getData();
        $keys = null;

        if ($form->getConfig()->hasAttribute('filter_value_keys')) {
            $keys = array_merge($data, $form->getConfig()->getAttribute('filter_value_keys'));
        }
        $values = array('value' => array());

        foreach ($keys as $key => $value) {
            $values['value'][$key][] = $data[$key];
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $values['value'][$key][$k] = $v;
                }
            }
        }

        return $values;
    }
}

