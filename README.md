# Workflow

> [!WARNING]
> This is a prototype only and is not ready for production use.

## Overview

Workflow is a framework-agnostic workflow orchestration system with a pluggable driver architecture. It lets you define multi-step execution flows as composable graphs of steps connected by conditional links, then run them on different execution backends without changing your workflow definitions.

### Core concepts

| Concept | Description |
|---------|-------------|
| **Workflow** | A container that holds steps and links, defining the complete execution graph |
| **Step** | An individual unit of work — each step type handles a specific concern |
| **Link** | A connection between steps that determines execution flow, with optional conditions |
| **Port** | Named input/output points on a step, allowing branching and merging |
| **Driver** | A pluggable execution engine that runs the workflow graph |

### Built-in step types

- **Data** — Set or transform workflow data
- **Conditional** — Branch execution based on expressions
- **Filter** — Filter collections of data
- **Loop** — Iterate over data sets
- **Process** — Execute sub-workflows
- **Rule** — Apply rule-based logic
- **Lambda** — Run inline closures

### Execution drivers

- **Sync** — Runs workflows synchronously in-process (ships with Core)
- **Temporal** — Distributed, durable execution via [Temporal](https://temporal.io)
- **Laravel Workflow** — Queue-based execution via [Laravel Workflow](https://github.com/laravel-workflow/laravel-workflow)

### Integrations

The codebase follows a "super package" pattern with nested packages that provide additional step types:

- **AI / ML** — OpenAI, AWS Bedrock, Groq, Hugging Face
- **Search** — Google
- **Laravel** — HTTP, SQL, and other Laravel-specific steps
- **V8** — JavaScript execution via the V8 engine
- **CLI** — Python and Node support via cli process

### Key features

- **Expression evaluation** — Symfony Expression Language for conditions and dynamic values
- **Data querying** — JMESPath support for extracting and transforming data
- **Schema validation** — YAML schemas define and validate step configuration
- **Serializable state** — Execution state can be serialized for durable and distributed drivers
- **Determinism tracking** — Steps are marked deterministic or non-deterministic for replay compatibility

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
