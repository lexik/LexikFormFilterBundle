<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Filter\Doctrine;

use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\DBALQuery;
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class DBALQueryTest extends TestCase
{
    public function testHasJoinAlias()
    {
        $exprMock = $this
            ->getMockBuilder('Doctrine\DBAL\Query\Expression\ExpressionBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock = $this
            ->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock
            ->expects($this->any())
            ->method('createExpressionBuilder')
            ->will($this->returnValue($exprMock));

        $qb = new QueryBuilder($connectionMock);
        $qb->leftJoin('root', 'table_1', 't1');
        $qb->leftJoin('root', 'table_2', 't2');
        $qb->innerJoin('t2', 'table_22', 't22');

        $query = new DBALQuery($qb);

        $this->assertTrue($query->hasJoinAlias('t2'));
        $this->assertFalse($query->hasJoinAlias('t3'));
    }
}
