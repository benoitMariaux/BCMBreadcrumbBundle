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

### First step

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
            label: ARTICLE
            parent: articles

### Second step
In progress...
