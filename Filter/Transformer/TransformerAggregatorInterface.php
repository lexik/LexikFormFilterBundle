<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Transformer;

interface TransformerAggregatorInterface
{
    /**
     * Add filter transformer
     *
     * @param string                     $id          Service filter transformer id
     * @param FilterTransformerInterface $transformer Service filter transformer instance
     */
    public function addFilterTransformer($id, FilterTransformerInterface $transformer);

    /**
     * Get service filter transformer by id
     *
     * @param string $id Service id
     *
     * @throws \RuntimeException
     *
     * @return FilterTransformerInterface
     */
    public function get($id);
}
