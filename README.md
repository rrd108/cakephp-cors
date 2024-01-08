# cakephp-cors

A CakePHP 5 plugin for activate cors domain in your application with [Middleware](https://book.cakephp.org/5/en/controllers/middleware.html).

Forked from [ozee31/cakephp-cors] as it seems to abdoned.

[Learn more about CORS](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS)

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require rrd108/cakephp-cors
```

Then load the plugin with the command:

```
bin/cake plugin load Cors
```

## Changing the default settings

By default the plugin authorize cors for all origins, all methods and all headers and caches all for one day.
If you are happy with the default settings, you can skip this section.

If you want to change any of the values then create your own `config/cors.php` file at your project's `config` directory. In your config file, you should use only those keys that you want to change. It will be merged to the default one. So, for example, if you are happy with all the options, except `AllowOrigin` default, then you have to put this into your on config file.

```php
'Cors' => [
  'AllowOrigin' => ['http://localhost:5173', 'https://example.com'],
]
```

#### AllowOrigin ([Access-Control-Allow-Origin](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS#Access-Control-Allow-Origin))

A returned resource may have one Access-Control-Allow-Origin header, with the following syntax:

```php
'Cors' => [
    // Accept all origins
    'AllowOrigin' => true,
    // OR
    'AllowOrigin' => '*',

    // Accept one origin
    'AllowOrigin' => 'http://webmania.cc'

    // Accept many origins
    'AllowOrigin' => ['http://webmania.cc', 'https://example.com']
]
```

#### AllowCredentials ([Access-Control-Allow-Credentials](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS#Access-Control-Allow-Credentials))

The Access-Control-Allow-Credentials header Indicates whether or not the response to the request can be exposed when the credentials flag is true. When used as part of a response to a preflight request, this indicates whether or not the actual request can be made using credentials. Note that simple GET requests are not preflighted, and so if a request is made for a resource with credentials, if this header is not returned with the resource, the response is ignored by the browser and not returned to web content.

```php
'Cors' => [
    'AllowCredentials' => true,
    // OR
    'AllowCredentials' => false,
]
```

#### AllowMethods ([Access-Control-Allow-Methods](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS#Access-Control-Allow-Methods))

```php
'Cors' => [
    // string
    'AllowMethods' => 'POST',
    // OR array
    'AllowMethods' => ['GET', 'POST'],
]
```

#### AllowHeaders ([Access-Control-Allow-Headers](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS#Access-Control-Allow-Headers))

The Access-Control-Allow-Headers header is used in response to a preflight request to indicate which HTTP headers can be used when making the actual request.

```php
'Cors' => [
    // accept all headers
    'AllowHeaders' => true,

    // accept just authorization
    'AllowHeaders' => 'authorization',

    // accept many headers
    'AllowHeaders' => ['authorization', 'other-header'],
]
```

#### ExposeHeaders ([Access-Control-Expose-Headers](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS#Access-Control-Expose-Headers))

The Access-Control-Expose-Headers header lets a server whitelist headers that browsers are allowed to access. For example:

```php
'Cors' => [
    // nothing
    'ExposeHeaders' => false,

    // string
    'ExposeHeaders' => 'X-My-Custom-Header',

    // array
    'ExposeHeaders' => ['X-My-Custom-Header', 'X-Another-Custom-Header'],
]
```

#### MaxAge ([Access-Control-Max-Age](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS#Access-Control-Max-Age))

The Access-Control-Max-Age header indicates how long the results of a preflight request can be cached. For an example of a preflight request, see the above examples.

```php
'Cors' => [
    // no cache
    'MaxAge' => false,

    // 1 hour
    'MaxAge' => 3600,

    // 1 day
    'MaxAge' => 86400,
]
```
