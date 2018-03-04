<?php

namespace DeployTracker\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use \Phake;
use DeployTracker\ORM\Tools\Pagination\Paginator;
use DeployTracker\Validator\PaginationValidator;
use DeployTracker\Event\PostFindEvent;
use DeployTracker\EventListener\PostFindEventSubscriber;
use DeployTracker\Event\FilterEvents;

class PostFindEventSubscriberTest extends TestCase
{
    /**
     * @test
     */
    public function shouldValidatePagination()
    {
        $paginatorMock = Phake::mock(Paginator::class);
        $validatorMock = Phake::mock(PaginationValidator::class);

        $event = new PostFindEvent($paginatorMock);

        $subscriber = new PostFindEventSubscriber($validatorMock);

        $subscriber->onPostFindEvent($event);

        Phake::verify($validatorMock)->validate($paginatorMock);
    }

    /**
     * @test
     */
    public function shouldSubscribeToPostFindEvent()
    {
        $subscribedEvents = PostFindEventSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(FilterEvents::POST_FIND, $subscribedEvents);
    }
}
