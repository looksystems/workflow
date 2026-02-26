<?php

namespace Tests;

uses(TestCase::class)->in('Unit/Aws');
uses(TestCase::class)->in('Unit/Core');
uses(TestCase::class)->in('Unit/Google');
uses(TestCase::class)->in('Unit/Groq');
uses(TestCase::class)->in('Unit/Huggingface');
uses(TestCase::class)->in('Unit/OpenAI');
uses(TestCase::class)->in('Unit/Symfony');
uses(TestCase::class)->in('Unit/V8');

uses(LaravelTestCase::class)->in('Unit/Laravel');
uses(LaravelWorkflowTestCase::class)->in('Unit/Drivers/LaravelWorkflow');
uses(TestCase::class)->in('Unit/Drivers/Temporal');
