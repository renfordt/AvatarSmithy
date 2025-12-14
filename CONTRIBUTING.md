# Contributing to AvatarSmithy

Thank you for your interest in contributing to AvatarSmithy! This guide will help you understand the project architecture, testing procedures, and quality standards.

## Testing

### Running Tests

Run the complete test suite:

```bash
composer test
```

Run individual test suites:

```bash
composer test:unit        # PHPUnit tests
composer test:types       # PHPStan static analysis
composer test:lint        # Code style check
composer test:refacto     # Refactoring check
```

### Running Individual Tests

```bash
vendor/bin/phpunit --filter=TestClassName
vendor/bin/phpunit tests/path/to/TestFile.php
```

## Code Quality

This package maintains high code quality standards:

- **PHPStan Level Max**: Strictest static analysis
- **PSR-12**: Code style via Laravel Pint
- **Rector**: Automated refactoring and modernization
- **100% Strict Types**: All files use `declare(strict_types=1)`

### Code Formatting

Fix code style issues:

```bash
composer lint            # Auto-fix with Pint
composer refacto         # Apply Rector rules
```

## Architecture

### Core Components

- **Avatar** (`src/Avatar.php`): Static factory providing entry points (`engine()`, `for()`)
- **AvatarBuilder** (`src/AvatarBuilder.php`): Fluent API for configuration and generation
- **GeneratedAvatar** (`src/GeneratedAvatar.php`): Output wrapper with multiple format methods
- **EngineInterface** (`src/Engines/EngineInterface.php`): Contract for all avatar engines
- **AbstractEngine** (`src/Engines/AbstractEngine.php`): Base implementation with shared functionality

### Engine System

All engines implement `EngineInterface` and typically extend `AbstractEngine`. The engine system supports:

- **Fallback Chain**: Engines are executed sequentially until one succeeds
- **Content Type Detection**: Each engine defines its output format
- **Network Abstraction**: `AbstractEngine::fetchUrl()` handles remote API calls
- **Deterministic Generation**: Same seed always produces same result

### Available Engines

| Engine             | Location                                | Type   |
|--------------------|-----------------------------------------|--------|
| InitialsEngine     | `src/Engines/InitialsEngine.php`        | Local  |
| GravatarEngine     | `src/Engines/GravatarEngine.php`        | Remote |
| DiceBearEngine     | `src/Engines/DiceBearEngine.php`        | Remote |
| PixelEngine        | `src/Engines/PixelEngine.php`           | Local  |
| MultiColorPixelEngine | `src/Engines/MultiColorPixelEngine.php` | Local |
| BauhausEngine      | `src/Engines/BauhausEngine.php`         | Local  |
| GradientEngine     | `src/Engines/GradientEngine.php`        | Local  |

### Creating Custom Engines

Implement the `EngineInterface`:

```php
use Renfordt\AvatarSmithy\Engines\EngineInterface;

class CustomEngine implements EngineInterface
{
    public function generate(string $seed, ?string $name, int $size, array $options): ?string
    {
        // Return avatar content (SVG, PNG data, or URL)
        // Return null to trigger fallback
    }

    public function getContentType(): string
    {
        return 'image/svg+xml';
    }
}
```

Register in `AvatarBuilder::createEngine()`:

```php
protected function createEngine(string $engine): EngineInterface
{
    return match (strtolower($engine)) {
        'custom' => new CustomEngine(),
        // ... existing engines
    };
}
```

### Support Classes

**Name** (`src/Support/Name.php`)
- Parses names for initials extraction
- Generates deterministic colors from MD5 hash
- UTF-8 aware for international names
- `getInitials()` - Returns uppercase initials
- `getHexColor(int $offset)` - Derives color from name hash

**Color Handling**

Uses `renfordt/colors` package for color manipulation (HexColor, HSLColor classes). See `InitialsEngine::getColorSet()` for lightness adjustment pattern.

**SVG Generation**

Engines generating SVG use `meyfa/php-svg` library. See `InitialsEngine` for reference implementation.

## Implementation Patterns

### Fallback Chain

The builder executes engines sequentially until one succeeds:

1. Primary engine attempts generation
2. On exception or `null` return, next fallback is tried
3. Throws `RuntimeException` if all engines fail

### Engine Registration

New engines are registered in `AvatarBuilder::createEngine()` using a match expression. Add new engines here with a lowercase string identifier.

## Contribution Guidelines

Contributions are welcome! Please ensure:

1. **All tests pass**: `composer test`
2. **Code follows PSR-12**: `composer lint`
3. **PHPStan level max passes**: `composer test:types`
4. **Use strict types**: Add `declare(strict_types=1);` to all new files
5. **Add tests**: Include unit tests for new functionality
6. **Update documentation**: Reflect changes in README.md or CLAUDE.md

### Pull Request Process

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature-name`
3. Make your changes following the code quality standards
4. Run all quality checks: `composer test`
5. Commit your changes with clear, descriptive messages
6. Push to your fork and submit a pull request

### Code Style

- Use PHP 8.4+ features where appropriate
- Follow PSR-12 coding standards
- Maintain strict type declarations
- Write clear, self-documenting code
- Add PHPDoc blocks for complex methods

## Development Setup

### Prerequisites

- PHP 8.4 or higher
- Composer
- GD extension

### Installation

```bash
# Clone the repository
git clone https://github.com/renfordt/avatar-smithy.git
cd avatar-smithy

# Install dependencies
composer install

# Run tests to ensure everything works
composer test
```

## Questions or Issues?

- Open an issue on GitHub for bugs or feature requests
- Check existing issues before creating a new one
- Provide detailed information including PHP version and steps to reproduce

Thank you for contributing to AvatarSmithy!
