<?php

namespace BCM\BreadcrumbBundle\Exception;

class RouteNotFoundException extends \Exception
{
    public function __construct()
    {
        $this->message = 'need a route before building items';
    }
}