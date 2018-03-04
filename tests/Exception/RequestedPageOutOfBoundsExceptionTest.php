<?php

namespace DeployTracker\Tests\Exception;

use PHPUnit\Framework\TestCase;
use DeployTracker\Exception\RequestedPageOutOfBoundsException;

class RequestedPageOutOfBoundsExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBuildCorrectMessage()
    {
        $requestedPage = 2;
        $maxPage = 1;

        $exception = new RequestedPageOutOfBoundsException($requestedPage, $maxPage);

        $this->assertSame($requestedPage, $exception->getRequestedPage());
        $this->assertSame($maxPage, $exception->getMaxPage());

        $message = sprintf(
            'Requested page is out of bounds, got: %d, maximum page is: %d',
            $requestedPage,
            $maxPage
        );

        $this->assertSame($message, $exception->getMessage());
    }
}
