<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Event\Listener;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Lexik\Bundle\FormFilterBundle\Event\Listener\PrepareListener;
use PHPUnit\Framework\TestCase;

class PrepareListenerTest extends TestCase
{
    public function testGetForceCaseInsensitivity()
    {
        $listener = new PrepareListener();

        $pgPlatform = $this->getMockBuilder('Doctrine\DBAL\Platforms\PostgreSqlPlatform')->getMock();
        $myPlatform = $this->getMockBuilder('Doctrine\DBAL\Platforms\MySqlPlatform')->getMock();

        $connection = $this->getMockBuilder(
            Connection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->any())
            ->method('getDatabasePlatform')
            ->will($this->onConsecutiveCalls(
                $pgPlatform,
                $myPlatform,
                $pgPlatform,
                $myPlatform
            ));

        $entityManager = $this->getMockBuilder('\\' . EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connection));

        $queryBuilder = $this
            ->getMockBuilder('\\' . QueryBuilder::class)
            ->setConstructorArgs([$entityManager])
            ->setMethods(['getEntityManager'])
            ->getMock()
        ;

        $queryBuilder->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($entityManager));

        $this->assertTrue($listener->getForceCaseInsensitivity($queryBuilder));
        $this->assertFalse($listener->getForceCaseInsensitivity($queryBuilder));

        $queryBuilder = $this
            ->getMockBuilder(\Doctrine\DBAL\Query\QueryBuilder::class)
            ->setConstructorArgs([$connection])
            ->setMethods(['getConnection'])
            ->getMock()
        ;

        $queryBuilder->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connection));

        $this->assertTrue($listener->getForceCaseInsensitivity($queryBuilder));
        $this->assertFalse($listener->getForceCaseInsensitivity($queryBuilder));

        $this->assertSame($listener, $listener->setForceCaseInsensitivity(true));
        $this->assertTrue($listener->getForceCaseInsensitivity('should not matter here'));

        $this->assertSame($listener, $listener->setForceCaseInsensitivity(false));
        $this->assertFalse($listener->getForceCaseInsensitivity('should not matter here'));

        self::expectException(\InvalidArgumentException::class);

        $listener->setForceCaseInsensitivity('some string');
    }
}
