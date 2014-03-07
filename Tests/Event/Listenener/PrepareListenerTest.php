<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Event\Listener;

use Lexik\Bundle\FormFilterBundle\Event\Listener\PrepareListener;

class PrepareListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetForceCaseInsensitivity()
    {
        $listener     = new PrepareListener;

        $pgPlatform = $this->getMock('Doctrine\DBAL\Platforms\PostgreSqlPlatform');
        $myPlatform = $this->getMock('Doctrine\DBAL\Platforms\MySqlPlatform');

        $connection    = $this->getMock(
            'Doctrine\DBAL\Connection',
            array(),
            array(),
            '',
            false
        );
        $connection->expects($this->any())
            ->method('getDatabasePlatform')
            ->will($this->onConsecutiveCalls(
                $pgPlatform,
                $myPlatform,
                $pgPlatform,
                $myPlatform
            ));

        $entityManager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connection));

        $queryBuilder  = $this->getMock(
            '\Doctrine\ORM\QueryBuilder',
            array('getEntityManager'),
            array($entityManager)
        );

        $queryBuilder->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($entityManager));

        $this->assertTrue($listener->getForceCaseInsensitivity($queryBuilder));
        $this->assertFalse($listener->getForceCaseInsensitivity($queryBuilder));

        $queryBuilder  = $this->getMock(
            'Doctrine\DBAL\Query\QueryBuilder',
            array('getConnection'),
            array($connection)
        );

        $queryBuilder->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connection));

        $this->assertTrue($listener->getForceCaseInsensitivity($queryBuilder));
        $this->assertFalse($listener->getForceCaseInsensitivity($queryBuilder));

        $this->assertSame($listener, $listener->setForceCaseInsensitivity(true));
        $this->assertTrue($listener->getForceCaseInsensitivity('should not matter here'));

        $this->assertSame($listener, $listener->setForceCaseInsensitivity(false));
        $this->assertFalse($listener->getForceCaseInsensitivity('should not matter here'));

        $listener->setForceCaseInsensitivity('some string');
    }
}
