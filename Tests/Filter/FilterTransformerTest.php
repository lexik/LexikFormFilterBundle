<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Filter;

use Symfony\Component\HttpFoundation\Request;

use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\OtherFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\QueryBuilder;
use Lexik\Bundle\FormFilterBundle\Tests\TestCase;

class FilterTransformerTest extends TestCase
{
    public function testFilterValueKeysTransformer()
    {
        // use filter type options
        $form = $this->formFactory->create(new OtherFilterType());
        $filterQueryBuilder = new QueryBuilder();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $request = $this->craeteRequest(array('position' =>
                array('left_number' => 1, 'right_number' => 3)));
        $form->bindRequest($request);

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.name LIKE :name_param AND i.position <> :position_param';
        $filterQueryBuilder->buildQuery($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());
    }

    protected function createDoctrineQueryBuilder()
    {
        return $this->em->createQueryBuilder()
            ->select('i')
            ->from('Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity', 'i');
    }

    protected function craeteRequest($values)
    {
        return new Request(
            array(),
            array('item_filter' => $values),
            array(),
            array(),
            array(),
            array('REQUEST_METHOD' => 'POST')
        );
    }
}
