<?php

namespace DeployTracker\Exception;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RequestedPageOutOfBoundsException extends \Exception
{
    /**
     * @var int
     */
    private $requestedPage;

    /**
     * @var int
     */
    private $maxPage;

    /**
     * @param int $requestedPage
     * @param int $maxPage
     */
    public function __construct(int $requestedPage, int $maxPage)
    {
        $this->requestedPage = $requestedPage;
        $this->maxPage = $maxPage;

        $message = sprintf(
            'Requested page is out of bounds, got: %d, maximum page is: %d',
            $requestedPage,
            $maxPage
        );

        parent::__construct($message);
    }

    /**
     * @return int
     */
    public function getRequestedPage(): int
    {
        return $this->requestedPage;
    }

    /**
     * @return int
     */
    public function getMaxPage(): int
    {
        return $this->maxPage;
    }
}
