<?php

namespace DeployTracker\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use DeployTracker\EventListener\PreFindEventSubscriber;
use Symfony\Component\HttpFoundation\RequestStack;
use DeployTracker\Resolver\PageResolver;
use DeployTracker\Resolver\RepositoryFilterResolver;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use \Phake;
use DeployTracker\Event\PreFindEvent;
use DeployTracker\Repository\FilterableInterface;
use DeployTracker\Repository\DeploymentRepository;
use DeployTracker\Event\FilterEvents;

class PreFindEventSubscriberTest extends TestCase
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var EntityRepository
     */
    private $repositoryMock;

    /**
     * @var PreFindEventSubscriber
     */
    private $subscriber;

    public function setUp()
    {
        $this->requestStack = new RequestStack();
        $this->repositoryMock = Phake::mock(EntityRepository::class);
        $this->subscriber = new PreFindEventSubscriber(
            $this->requestStack,
            new PageResolver('page'),
            new RepositoryFilterResolver()
        );
    }

    /**
     * @test
     */
    public function shouldSetPageFromRequest()
    {
        $request = new Request();
        $request->query->set('page', 42);

        $this->requestStack->push($request);

        $event = $this->createEvent();

        $this->subscriber->onPreFindEvent($event);

        self::assertSame(42, $event->getPage());
    }

    /**
     * @test
     */
    public function shouldSetFiltersIfRepositoryImplementsFilterableInterface()
    {
        $request = new Request();
        $request->query->set('some_filter', 'some_value');

        $this->requestStack->push($request);

        $this->repositoryMock = Phake::mock(EntityRepository::class);

        $event = $this->createEvent();

        $this->subscriber->onPreFindEvent($event);

        self::assertCount(0, $event->getFilters());

        $this->repositoryMock = Phake::mock(DeploymentRepository::class);

        Phake::when($this->repositoryMock)->getAvailableFilters->thenReturn(['some_filter']);

        $event = $this->createEvent();

        $this->subscriber->onPreFindEvent($event);

        self::assertEquals(['some_filter' => 'some_value'], $event->getFilters());
    }

    /**
     * @test
     */
    public function shouldMergeFilters()
    {
        $request = new Request();
        $request->query->set('some_filter', 'some_value');

        $this->requestStack->push($request);

        $this->repositoryMock = Phake::mock(DeploymentRepository::class);

        Phake::when($this->repositoryMock)->getAvailableFilters->thenReturn(['some_filter']);

        $event = $this->createEvent(1, ['some_other' => 'other_value']);

        $this->subscriber->onPreFindEvent($event);

        self::assertEquals(['some_filter' => 'some_value', 'some_other' => 'other_value'], $event->getFilters());
    }

    /**
     * @test
     */
    public function shouldSubscribeToPreFindEvent()
    {
        $subscribedEvents = PreFindEventSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(FilterEvents::PRE_FIND, $subscribedEvents);
    }

    /**
     * @param int $page
     * @param array $filters
     * @return PreFindEvent
     */
    private function createEvent(int $page = 1, array $filters = []): PreFindEvent
    {
        return new PreFindEvent($this->repositoryMock, $page, $filters);
    }
}
