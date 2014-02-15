<?php

namespace BCM\BreadcrumbBundle\Service;

use BCM\BreadcrumbBundle\Exception\RouteNotFoundException;
use BCM\BreadcrumbBundle\Model\Item;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Templating\EngineInterface;

class BreadcrumbManager
{
    const KEY_LABEL = 'label';
    const KEY_PARENT = 'parent';

    protected $request;
    protected $router;
    protected $templateEngine;

    protected $items;
    protected $parameters;

    public function __construct(Request $request, RouterInterface $router, EngineInterface $templateEngine)
    {
        $this->request = $request;
        $this->router = $router;
        $this->templateEngine = $templateEngine;
        $this->items = new ArrayCollection();
    }

    public function render($parameters = array())
    {
        $this->parameters = array_merge($this->request->attributes->all(), $parameters);

        $currentRoute = $this->router->getRouteCollection()->get($this->request->get('_route'));

        if (!$currentRoute) {
            throw new RouteNotFoundException();
        }

        $this->buildRecursivelyItemsByRoute($currentRoute, $this->request->get('_route'));

        return $this->templateEngine->render('@BCMBreadcrumb/bcm-breadcrumb.html.twig', array(
            'items' => $this->reverseItems()
        ));
    }

    protected function buildRecursivelyItemsByRoute(Route $route, $routeName)
    {
        if ($route->hasDefault(self::KEY_LABEL)) {
            $item = new Item();
            $label = $this->generateLabel($route->getDefault(self::KEY_LABEL));
            $item->setLabel($label);

            $path = $this->generatePathRoute($route, $routeName);
            $item->setPath($path);
            $this->items->add($item);

            if ($route->hasDefault(self::KEY_PARENT)) {
                $parentRoute = $this->router->getRouteCollection()->get($route->getDefault(self::KEY_PARENT));

                if (!$parentRoute) {
                    throw new RouteNotFoundException();
                }

                $this->buildRecursivelyItemsByRoute($parentRoute, $route->getDefault(self::KEY_PARENT));
            }
        }

        return $this->items;
    }

    protected function generatePathRoute(Route $route, $routeName)
    {
        $pattern = $route->getPath();
        $params = array();

        preg_match_all('`\{(.+)\}`isU', $pattern, $out);

        if ($patternParameters = $out[0]) {
            if (is_array($patternParameters)) {
                foreach ($patternParameters as $param) {
                    $paramName = trim(trim($param,'}'),'{');
                    if (isset($this->parameters[$paramName])) {
                        $params[$paramName] = $this->parameters[$paramName];
                    }
                }
            }
        }

        return $this->router->generate($routeName, $params);
    }

    protected function generateLabel($label)
    {
        preg_match_all('`\{(.+)\}`isU', $label, $out);

        if ($labelParameters = $out[0]) {
            if (is_array($labelParameters)) {
                foreach ($labelParameters as $param) {
                    $paramName = trim(trim($param,'}'),'{');
                    if (isset($this->parameters[$paramName])) {
                        $label = str_replace($param, $this->parameters[$paramName], $label);
                    }
                }
            }
        }

        return $label;
    }

    protected function reverseItems()
    {
        $items = array_reverse($this->items->toArray());
        return new ArrayCollection($items);
    }
}
