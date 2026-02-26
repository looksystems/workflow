# AWS Bedrock Integration for Look Workflows

This package provides AWS Bedrock integration for the Look Workflows system, allowing you to use Amazon Bedrock's foundation models within your workflows.

## Installation

```bash
composer require looksystems/workflow-aws
```

## Configuration

Set the following environment variables:

```bash
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_REGION=eu-west-1  # Optional, defaults to eu-west-1
```

## Usage

### Basic Usage

```php
use Look\Workflows\Aws\Bedrock;

$step = Bedrock::make('anthropic.claude-v2')
    ->message('Hello, how can you help me today?')
    ->temperature(0.7)
    ->maxTokens(1000);

$result = $step->execute();
```

### Available Models

The Bedrock step supports various foundation models:

- `anthropic.claude-v2`
- `anthropic.claude-instant-v1`
- `amazon.titan-text-express-v1`
- `ai21.j2-ultra-v1`
- `ai21.j2-mid-v1`
- `cohere.command-text-v14`
- `meta.llama2-13b-chat-v1`

### Configuration Options

#### Model and Region

```php
$step = Bedrock::make('anthropic.claude-v2', 'us-east-1');
// or
$step = Bedrock::make()->model('anthropic.claude-v2', 'us-east-1');
```

#### Messages

Add messages to the conversation:

```php
// Single message
$step->message('What is the weather like?', 'user');

// Multiple messages
$step->messages([
    ['role' => 'user', 'content' => 'What is 2+2?'],
    ['role' => 'assistant', 'content' => '2+2 equals 4'],
    ['role' => 'user', 'content' => 'What is 3+3?']
]);
```

#### Parameters

```php
$step->temperature(0.5)      // Controls randomness (0.0 to 1.0)
     ->maxTokens(2000);      // Maximum tokens in response
```

#### Response Types

Control how the response is formatted:

```php
// Return raw Bedrock response
$step->respondWithRaw();

// Return only the message content (default)
$step->respondWithMessage();

// Return all messages including the response
$step->respondWithAllMessages();
```

### Integration with Workflows

```php
use Look\Workflows\Core\Workflow;
use Look\Workflows\Aws\Bedrock;

$workflow = Workflow::make()
    ->name('Customer Support')
    ->steps([
        Bedrock::make('anthropic.claude-v2')
            ->label('Generate Response')
            ->message('Help me understand this product issue')
            ->temperature(0.3)
            ->maxTokens(500)
            ->respondWithMessage(),
    ]);
```

### Error Handling

The step will return an error result if:
- AWS credentials are not configured
- The Bedrock service returns an error
- Network issues occur

```php
$result = $step->execute();

if ($result->isError()) {
    $error = $result->getError();
    // Handle error
}
```

## Testing

The package includes unit tests. Run them with:

```bash
composer test
```

## Requirements

- PHP 8.1 or higher
- AWS SDK for PHP v3
- Valid AWS credentials with Bedrock access
- Appropriate model access in your AWS account

## Security

- Never commit AWS credentials to version control
- Use environment variables or AWS IAM roles
- Ensure your AWS credentials have minimal required permissions
- Monitor API usage to control costs

## Support

For issues or questions, please open an issue in the Look Workflows repository.