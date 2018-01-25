<?php

namespace DeployTracker\Tests\Manager;

use PHPUnit\Framework\TestCase;
use Phake;
use Doctrine\ORM\EntityRepository;
use DeployTracker\Manager\RepositoryManager;
use DeployTracker\Result\FilteredResult;
use DeployTracker\ORM\Tools\Pagination\Paginator;
use DeployTracker\Event\FilterEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;
use DeployTracker\Event\PreFindEvent;
use DeployTracker\Event\PostFindEvent;

class RepositoryManagerTest extends TestCase
{
    /**
     * @test
     */
    public function shouldDispatchFilterEvents()
    {
        $repository = Phake::mock(EntityRepository::class);
        $paginator = Phake::mock(Paginator::class);
        $dispatcher = new EventDispatcher();

        $filters = ['some_filter' => 'some_value'];
        $eventsDispatched = 0;

        $dispatcher->addListener(
            FilterEvents::PRE_FIND,
            function (PreFindEvent $event) use (&$eventsDispatched, $repository, $filters) {
                $eventsDispatched++;
                $event->setPage(5);
                $event->setFilters($filters);
                TestCase::assertSame($repository, $event->getRepository());
            }
        );

        $dispatcher->addListener(
            FilterEvents::POST_FIND,
            function (PostFindEvent $event) use (&$eventsDispatched, $filters) {
                $eventsDispatched++;
                $this->assertSame($filters, $event->getFilters());
            }
        );

        Phake::when($repository)->findSomething->thenReturn($paginator);

        $manager = new RepositoryManager($repository, $dispatcher);

        $result = $manager->findSomething();

        Phake::verify($repository)->findSomething(5, $filters);

        $this->assertInstanceOf(FilteredResult::class, $result);
        $this->assertSame(2, $eventsDispatched);
        $this->assertSame($filters, $result->getFilters());
    }
}
