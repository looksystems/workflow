# Workflow

## Getting started

First add the repository to your composer.json:

```
"repositories": {
    "look-workflow": {
        "type": "vcs",
        "url": "https://github.com/looksystems/workflow.git"
    }
}
```

And then require the package as usual:

```
composer require looksystems/workflow
```

## Framework agnostic

The core workflow package is framework agnostic.

## Nested packages

**IMPORTANT: when add new functionality, please follow the "nested package" approach detailed below...**

This is a "super package" which provides both the core workflow package and a range nested packages for integrating with a range of api, data, services, etc.

In the src folder of the nested package, is treated as the root of that package and contains a README.md and composer.json.

In the main composer.json, the packaged is added to the "replaces" section and additional dependencies for the nested package should be define in the "suggest" (and "require-dev" section, assuming there are unit tests). That way, downstream clients can choose whether to install additional dependencies as required.

In the future, it would be possible to write a github workflow to split up this repo into the related "read only" nested packages. This is the same strategy that the laravel framework takes (and there are some useful scripts that can assist with repo. splitting there).

## Running tests

Install the composer dependencies.

Copy phpunit.xml.dist to phpunit.xml and set your local environment variables.

And set-up testbench:

```
    ./vendor/bin/testbench package:create-sqlite-db
    ./vendor/bin/testbench vendor:publish --force --no-ansi --no-interaction --provider="Workflow\Providers\WorkflowServiceProvider" --tag="migrations"
    ./vendor/bin/testbench migrate --no-ansi --no-interaction
```

To run all tests:

```
    composer run tests
```

To run a single test:

```
    composer run test [test path name]
```

To run a single test case:

```
    composer run filter [test method name]
```


## Test coverage

To run generate test coverage report in build/coverage/

```
    composer run coverage
```

You will need to have installed xdebug for code coverage to work.

```
    pecl install xdebug
```

Example xdebug.ini file:


```
    zend_extension=xdebug.so

    [xebug]
    xdebug.enabled=0
    xdebug.remote_enable=true
    xdebug.max_nesting_level=1024
    xdebug.mode=profile,coverage
    xdebug.start_with_request=trigger
```

### Where's the code?

 * [Unit](./tests/Unit)        - unit tests
