# Symfony D-Media Bundle

## Introduction

Symfony D-Media Bundle is a package that provides JSON REST HTTP API
for frontend media manager library and services to use Medias on backend.

Frontend Package: [vue-d-media](https://github.com/djvue/vue-d-media)

Usage example: [symfony-d-media-bundle-example](https://github.com/djvue/symfony-d-media-bundle-example)

Usage example demo: [demo](https://d-media.webtm.ru)

## Installation

Install with composer

```shell
composer require djvue/d-media-bundle
```

Requires PHP >=8.0 and Symfony >=5.2

You may also need to install frontend components.
See instruction in frontend repository [vue-d-media](https://github.com/djvue/vue-d-media).

## Getting started

- Add bundle

config/bundles.php
```PHP
return [
    ...,
    Djvue\DMediaBundle\DMediaBundle::class => ['all' => true],
];
```

- Import routes

config/routes.yaml
```yaml
media:
    resource: '@DMediaBundle/Resources/config/routes/media.yaml'
    prefix: '/api/media' #your prefix equal to frontend library api.config.prefix
    trailing_slash_on_root: false
    name_prefix: board_media_
```

- Configure (optional)

config/packages/d_media.yaml
```yaml
d_media:
    filterable_entities:
        - workspace
    storage:
        public_url: /storage/medias
        directory: /uploads
    library:
        image_extensions: png, jpg, jpeg, webp
```

## Help services

SomeClass.php

```PHP
public function __construct(
    private MediaService $mediaService,
    private MediaEntityService $mediaEntityService,
) {
}
```

## Security

Bundle controller uses symfony/security.
To control access add voters: MediaVoter and MediaGetListVoter.

See example in symfony example repository
[symfony-d-media-bundle-example](https://github.com/djvue/symfony-d-media-bundle-example)
