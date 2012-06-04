<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Transformer;

use Symfony\Component\Form\FormInterface;

/**
 * Transform a date data in right format
 *
 * @author <g.gauthier@lexik.com>
 *
 */
class FilterDateTransformer implements FilterTransformerInterface
{
    /**
     * {@inheritDoc}
     * @see Lexik\Bundle\FormFilterBundle\Filter\Transformer.FilterTransformerInterface::transform()
     */
    public function transform(FormInterface $form)
    {
        $data   = $form->getData();
        $config = $form->getConfig();
        $keys   = array();

        if ($config->hasAttribute('filter_value_keys')) {
            $keys = $config->getAttribute('filter_value_keys');
        }
        $values = array('value' => array());

        foreach ($keys as $key) {
            $values['value'][$key] = $data[$key];
        }

        return $values;
    }
}

