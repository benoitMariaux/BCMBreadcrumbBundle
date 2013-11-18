<?php

namespace BCM\BreadcrumbBundle\Service;

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

        $this->buildRecursivelyItemsByRoute($currentRoute, $this->request->get('_route'));

        return $this->templateEngine->render('@BCMBreadcrumb/index.html.twig', array(
            'items' => $this->reverseItems()
        ));
    }

    protected function buildRecursivelyItemsByRoute(Route $route, $routeName)
    {
        if ($route->hasDefault(self::KEY_LABEL)) {
            $item = new Item();
            $item->setLabel($route->getDefault(self::KEY_LABEL));

            $path = $this->routeToPath($route, $routeName);
            $item->setPath($path);
            $this->items->add($item);

            if ($route->hasDefault(self::KEY_PARENT)) {
                $parentRoute = $this->router->getRouteCollection()->get($route->getDefault(self::KEY_PARENT));
                $this->buildRecursivelyItemsByRoute($parentRoute, $route->getDefault(self::KEY_PARENT));
            }
        }

        return $this->items;
    }

    protected function routeToPath(Route $route, $routeName)
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

    public function replaceLabel($label, $replace)
    {
        $label = trim($label);
        $replace = trim($replace);

        foreach ($this->items as $key => $item) {
            if ($item instanceof Item && $item->getLabel() == $label) {
                $item->setLabel($replace);
                $this->items[$key] = $item;
            }
        }
    }

    protected function reverseItems()
    {
        $items = array_reverse($this->items->toArray());
        return new ArrayCollection($items);
    }
}