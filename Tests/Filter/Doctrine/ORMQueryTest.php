<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Filter\Doctrine;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Doctrine\ORM\QueryBuilder;

/**
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class ORMQueryTest extends \PHPUnit_Framework_TestCase
{
    public function testHasJoinAlias()
    {
        $emMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $emMock
            ->expects($this->any())
            ->method('getExpressionBuilder')
            ->will($this->returnValue(new \Doctrine\ORM\Query\Expr()));

        $qb = new QueryBuilder($emMock);
        $qb->from('Root', 'r');
        $qb->leftJoin('r.association1', 'a1');
        $qb->leftJoin('r.association2', 'a2');
        $qb->innerJoin('a2.association22', 'a22');

        $query = new ORMQuery($qb);

        $this->assertTrue($query->hasJoinAlias('a2'));
        $this->assertFalse($query->hasJoinAlias('a3'));
    }
}
