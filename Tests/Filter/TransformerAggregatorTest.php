<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter;

use Lexik\Bundle\FormFilterBundle\Filter\Transformer\TransformerAggregator;
use Lexik\Bundle\FormFilterBundle\Filter\Transformer\FilterTextTransformer;
use Lexik\Bundle\FormFilterBundle\Tests\TestCase;

/**
 * TransformerAggregator tests.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class TransformerAggregatorTest extends TestCase
{
    public function testGet()
    {
        $textTransFormer = new FilterTextTransformer();

        $aggregator = new TransformerAggregator();
        $aggregator->addFilterTransformer('transformer.text', $textTransFormer);

        $this->assertSame($textTransFormer, $aggregator->get('transformer.text'));

        $this->setExpectedException('RuntimeException');
        $aggregator->get('undefined.transformer.id');
    }
}