BCMBreadcrumbBundle
===================

BCMBreadcrumbBundle allows you to generate breadcrumbs easily for your Symfony project.

Installation
------------

With [composer](http://packagist.org), add:
```js
{
    require: {
        "benoitmariaux/bcm-breadcrumbbundle": "dev-master"
    }
}
```    

Then enable it in your kernel:
```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        ...
        new BCM\BreadcrumbBundle\BCMBreadcrumbBundle(),
        ...
```

Usage
-----

### First step : the routes

You have to configure two attributes (`label` and `parent`) to `defaults` in routes you want to add to your breadcrumb :
```yaml
homepage:
    pattern: /
    defaults:
        _controller: AcmeDemoBundle:Default:home
        label: homepage # no parent for homepage

articles:
    pattern: /articles
    defaults:
        _controller: AcmeDemoBundle:Article:list
        label: ARTICLES
        parent: homepage

article:
    pattern: /articles/show/{article_id}
    defaults:
        _controller: AcmeDemoBundle:Article:article
        label: '{article_title}'
        parent: articles
```

### Second step : the controller
Inject all parameters you need for current breadcrumb routes and labels
```php
$breadcrumb = $this->get('bcm_breadcrumb.manager')->render(array(
    'article_title' => $article->getTitle(), // useful for article route label
    'article_id' => $article->getId() // useful for article route pattern
));

return $this->render('AcmeDemoBundle:Default:article.html.twig', array(
    'article' => $article,
    'breadcrumb' => $breadcrumb
));
```

### Last step : the view
```twig
{{ breadcrumb|raw }}
```

View
----
The default view is:
`vendor/benoitmariaux/bcm-breadcrumbbundle/BCM/BreadcrumbBundle/Resources/views/bcm-breadcrumb.html.twig`

You can overwrite it by creating your own here:
`app/Resources/BCMBreadcrumbBundle/views/bcm-breadcrumb.html.twig`

Label translation
-----------------

You can define translations for your labels with the default domain `breadcrumb`:
* breadcrumb.fr.xliff
* breadcrumb.de.xliff
