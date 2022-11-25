# FeaturIT SDK for PHP

PHP client for the FeaturIT Feature Flag management platform.

## Description

This package aims to simplify the integration of the FeaturIT API in a PHP project.

## Getting started

### Dependencies

* PHP >= 8.0
* psr/http-client-implementation
* psr/simple-cache-implementation

### Installing

`composer require featurit/featurit-sdk-php`

If there's no package providing psr/http-client-implementation, 
visit https://packagist.org/providers/psr/http-client-implementation and choose the package
that better suits your project.

If there's no package providing psr/simple-cache-implementation,
visit https://packagist.org/providers/psr/simple-cache-implementation and choose the package
that better suits your project.

### Usage

```
$featurit = new \Featurit\Client\Featurit(
    'your-tenant-subdomain', 
    'your-environment-key'
);

$isFeatureFlagActive = $featurit->isActive('YOUR_FEATURE_NAME');

if ($isFeatureFlagActive) {
    your_feature_code();
}
```

### Authors

FeaturIT

https://www.featurit.com

featurit_tech@gmail.com