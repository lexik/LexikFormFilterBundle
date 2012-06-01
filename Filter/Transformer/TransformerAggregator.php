<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Transformer;

/**
 * Transformer aggregator
 *
 * @author <g.gauthier@lexik.com>
 *
 */
class TransformerAggregator implements TransformerAggregatorInterface
{
    /**
     * @var array
     */
    protected $filterTransformers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->filterTransformers = array();
    }

    /**
     * Add filter transformer
     *
     * @param string                     $id          Service filter transformer id
     * @param FilterTransformerInterface $transformer Service filter transformer instance
     */
    public function addFilterTransformer($id, FilterTransformerInterface $transformer)
    {
        if (!(isset($this->filterTransformers[$id]))) {
            $this->filterTransformers[$id] = $transformer;
        }
    }

    /**
     * Get service filter transformer by id
     *
     * @param string $id Service id
     *
     * @throws \RuntimeException
     *
     * @return Lexik\Bundle\FormFilterBundle\Filter\Transformer\FilterTransformerInterface
     */
    public function get($id)
    {
        if (!isset($this->filterTransformers[$id])) {
            throw new \RuntimeException(sprintf('No filter transformer found with id "%s"', $id));
        }

        return $this->filterTransformers[$id];
    }
}

