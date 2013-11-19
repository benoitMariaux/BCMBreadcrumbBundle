BCMBreadcrumbBundle
===================

BCMBreadcrumbBundle allows you to generate breadcrumbs easily for your Symfony project.

Installation
------------

With [composer](http://packagist.org), add:

    {
        require: {
            "benoitmariaux/bcm-breadcrumbbundle": "dev-master"
        }
    }

Then enable it in your kernel:

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            ...
            new BCM\BreadcrumbBundle\BCMBreadcrumbBundle(),
            ...

Configuration
-------------

In progress...

Usage
-----

### First step : the routes

You have to configure two attributes (`label` and `parent`) to `defaults` in routes you want to add to your breadcrumb :

    homepage:
        pattern: /
        defaults:
            _controller: AcmeBundle:Default:home
            label: homepage # no parent for homepage

    articles:
        pattern: /articles
        defaults:
            _controller: AcmeBundle:Article:list
            label: ARTICLES
            parent: homepage

    article:
        pattern: /articles/show/{article_id}
        defaults:
            _controller: AcmeBundle:Article:article
            label: '{article_title}'
            parent: articles

### Second step : the controller
Inject all parameters you need for current breadcrumb routes and labels

    $breadcrumb = $this->get('bcm_breadcrumb.manager')->render(array(
        'article_title' => $article->getTitle(), // useful for article route label
        'article_id' => $article->getId() // useful for article route pattern
    ));

    return $this->render('AcmeDemoBundle:Default:article.html.twig', array(
        'article' => $article,
        'breadcrumb' => $breadcrumb
    ));

### Last step : the view

    {{ breadcrumb|raw }}

### Label translation

You can define translations for your labels with the default domain `breadcrumb`:
* breadcrumb.fr.xliff
* breadcrumb.de.xliff
