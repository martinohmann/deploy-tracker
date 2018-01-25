<?php

namespace DeployTracker\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use DeployTracker\Event\FilterEvents;
use DeployTracker\Validator\PaginationValidator;
use DeployTracker\Event\PostFindEvent;
use DeployTracker\Exception\RequestedPageOutOfBoundsException;

class PostFindEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var PaginationValidator
     */
    private $validator;

    /**
     * @param PaginationValidator $validator
     */
    public function __construct(PaginationValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param PostFindEvent $event
     * @return void
     * @throws RequestedPageOutOfBoundsException
     */
    public function onPostFindEvent(PostFindEvent $event)
    {
        $this->validator->validate($event->getPaginator());
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FilterEvents::POST_FIND => 'onPostFindEvent',
        ];
    }
}
