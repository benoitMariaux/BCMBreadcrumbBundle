<?php

namespace BM\BreadcrumbBundle\Service;

use BM\BreadcrumbBundle\Model\Item;
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

    public function __toString()
    {
        return $this->render();
    }

    public function build($parameters = null)
    {
        if ($parameters) {
            $this->setParameters($parameters);
        }

        $this->setParameters(array_merge($this->request->attributes->all(), $this->getParameters()));

        $currentRoute = $this->router->getRouteCollection()->get($this->request->get('_route'));

        $this->buildRecursivelyItemsByRoute($currentRoute, $this->request->get('_route'));
    }

    protected function buildRecursivelyItemsByRoute(Route $route, $routeName)
    {
        if ($route->hasDefault(self::KEY_LABEL)) {
            $item = new Item();
            $item->setLabel($route->getDefault(self::KEY_LABEL));

            $path = $this->routeToPath($route, $routeName);
            $item->setPath($path);
            $this->addItem($item);

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

        foreach ($this->getItems() as $key => $item) {
            if ($item->getLabel() == $label) {
                $item->setLabel($replace);
                $this->items[$key] = $item;
            }
        }
    }

    public function render()
    {
        return $this->templateEngine->render('@BMBreadcrumb/index.html.twig', array(
            'items' => $this->reversedItems()
        ));
    }

    public function addItem(Item $item)
    {
        $this->items->add($item);
    }

    public function setItems($items)
    {
        $this->items = $items;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function reversedItems()
    {
        $items = array_reverse($this->getItems()->toArray());
        return new ArrayCollection($items);
    }

    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    public function getParameters()
    {
        return $this->parameters;
    }
}