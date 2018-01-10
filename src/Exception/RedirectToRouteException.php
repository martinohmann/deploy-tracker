<?php

namespace DeployTracker\Exception;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RedirectToRouteException extends \Exception
{
    /**
     * @var string
     */
    private $routeName;
    /**
     * @var array
     */
    private $parameters;

    /**
     * @var int
     */
    private $referenceType;

    /**
     * @param string $routeName
     * @param array $parameters
     * @param int $referenceType
     */
    public function __construct(
        string $routeName,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ) {
        $this->routeName = $routeName;
        $this->parameters = $parameters;
        $this->referenceType = $referenceType;

        $message = sprintf(
            'Redirect to route "%s" with params "%s" required',
            $routeName,
            json_encode($parameters)
        );

        parent::__construct($message);
    }

    /**
     * @return string
     */
    public function getRouteName(): string
    {
        return $this->routeName;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return int
     */
    public function getReferenceType(): int
    {
        return $this->referenceType;
    }
}
