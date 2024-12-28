# FeaturIT SDK for PHP

<p>
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/featurit/featurit-sdk-php.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/featurit/featurit-sdk-php"><img src="https://img.shields.io/packagist/dt/featurit/featurit-sdk-php" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/featurit/featurit-sdk-php"><img src="https://img.shields.io/packagist/v/featurit/featurit-sdk-php" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/featurit/featurit-sdk-php"><img src="https://img.shields.io/packagist/l/featurit/featurit-sdk-php" alt="License"></a>
</p>

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

### Basic Usage

```
$featurit = new \Featurit\Client\Featurit(
    'my-tenant-subdomain', 
    'my-environment-key'
);

$isFeatureFlagActive = $featurit->isActive('MY_FEATURE_NAME');

if ($isFeatureFlagActive) {
    my_feature_code();
}
```

### Segmentation Usage

This is useful when you want to show different versions of your features
to your users depending on certain attributes.

```
$featurit = new \Featurit\Client\Featurit(
    'my-tenant-subdomain', 
    'my-environment-key'
);

$userContext = new \Featurit\Client\Modules\Segmentation\DefaultFeaturitUserContext(
    '1234',
    '123af503',
    '192.168.1.1',
    [
        'role' => 'ADMIN',
        'email' => 'featurit.tech@gmail.com',
    ] 
);

$featurit->setUserContext($userContext);

$isFeatureFlagActive = $featurit->isActive('MY_FEATURE_NAME');

if ($isFeatureFlagActive) {
    $featureVersion = $featurit->version('MY_FEATURE_NAME');
    
    if ($featureVersion == 'v1') {
        my_feature_v1_code();
    } else if ($featureVersion == 'v2') {
        my_feature_v2_code();
    }
}
```

### Creating a UserContextProvider

In some cases you want to fill the UserContext data once and 
forget about it when checking for feature flags.

If that is your case you can implement your own UserContextProvider
and pass it to the Featurit client constructor (we recommend using our builder 
in order to create a new Featurit client).

```
class MyFeaturitUserContextProvider implements FeaturitUserContextProvider

    public function getContext(): FeaturitUserContext
    {
        $contextData = get_my_context_data();
        
        return new DefaultFeaturitUserContext(
            $contextData['userId'],
            $contextData['sessionId'],
            $contextData['ipAddress'],
            [
                'role' => $contextData['role'],
                ...
            ]
        );
    }
}
```

### Event Tracking

In order to track some event in your application, you can add this once the event has happened:

```
$featurit = new \Featurit\Client\Featurit(
    'my-tenant-subdomain', 
    'my-environment-key'
);

$userContext = new \Featurit\Client\Modules\Segmentation\DefaultFeaturitUserContext(
    '1234',
    '123af503',
    '192.168.1.1',
    [
        'role' => 'ADMIN',
        'email' => 'featurit.tech@gmail.com',
    ] 
);

$featurit->setUserContext($userContext);

$featurit->trackPerson();

// trackPerson will track a new Person with the data set in the FeaturitUserContext.

$featurit->track('MY_EVENT_NAME', [
    'a_property_name' => 'a_property_value',
    'another_property_name' => 'another_property_value',
]);
```

All the events and people you track in the same request will be accumulated and associated to the current
FeaturitUserContext, if for some reason you want to send the data immediately, you can do as follows:

```
$featurit->flush();
```

### Authors

FeaturIT

https://www.featurit.com

featurit.tech@gmail.com