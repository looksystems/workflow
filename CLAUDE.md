# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

### Testing
```bash
composer run tests           # Run all tests
composer run test [path]     # Run specific test file/directory
composer run filter [method] # Run specific test method
composer run debug          # Run tests with debug output
```

### Code Quality
```bash
composer run pint           # Fix code style (Laravel Pint)
composer run analyse        # Static analysis (PHPStan level 8)
```

### Coverage
```bash
composer run coverage       # Generate coverage report
composer run coverage-html  # HTML coverage report at build/coverage/html/
```

## Architecture

This is a workflow orchestration system with a pluggable driver architecture:

### Core Concepts
- **Workflows**: Container for steps and links that define execution flow
- **Steps**: Individual execution units (Data, Conditional, Filter, Loop, Process, etc.)
- **Links**: Connect steps and determine execution flow with optional conditions
- **Drivers**: Pluggable execution engines (Sync, Temporal, Laravel Workflow)

### Package Structure
The codebase uses a "super package" pattern with nested packages in `src/`:
- `Core/`: Framework-agnostic workflow engine
- `AWS/`: Bedrock AI integration
- `Drivers/`: Temporal and Laravel Workflow drivers
- `Google/`, `Groq/`, `Huggingface/`, `OpenAI/`: AI/ML integrations
- `Laravel/`: Laravel-specific components
- `V8/`: JavaScript execution support

### Key Design Patterns
1. **Schema-Driven Steps**: Each step type has a YAML schema in `schemas/` for validation
2. **Driver Interface**: All execution drivers implement `ExecutionDriverInterface`
3. **Deterministic vs Non-Deterministic**: Steps are marked for replay compatibility
4. **Expression Evaluation**: Uses Symfony Expression Language for conditions
5. **Data Querying**: JMESPath support for data extraction

### Testing Approach
- Uses Pest PHP framework
- Mock services for external dependencies
- Test files mirror source structure in `tests/`
- Integration tests for driver implementations