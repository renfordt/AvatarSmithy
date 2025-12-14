![Preview Image](preview.png "Preview")

# AvatarSmithy

**Forge beautiful avatars with ease.**

A modern PHP 8.4+ library that bundles multiple avatar generation engines into a unified, fluent API. Create stunning avatars from user data with automatic fallback support and extensive customization options.

## Features

- **Multiple Engines**: Choose from 7 built-in avatar generation engines
- **Fluent API**: Intuitive builder pattern for easy configuration
- **Smart Fallback**: Automatic failover between engines for reliability
- **Flexible Output**: Export as HTML img tags, Base64, URLs, or HTTP responses
- **Highly Customizable**: Extensive styling options per engine
- **Framework Agnostic**: Works standalone or integrates with Laravel/Symfony
- **Type Safe**: Built with strict types and PHPStan level max
- **Modern PHP**: Requires PHP 8.4+ with latest language features

## Requirements

- PHP 8.4 or higher
- GD extension
- Composer

## Installation

Install via Composer:

```bash
composer require renfordt/avatar-smithy
```

## Quick Start

### Basic Usage

Generate an avatar using the initials engine:

```php
use Renfordt\AvatarSmithy\Avatar;

// Simple avatar with email seed
$avatar = Avatar::engine('initials')
    ->seed('john.doe@example.com')
    ->name('John Doe')
    ->generate();

echo $avatar; // Outputs HTML img tag
```

### Smart User Factory

Automatically extract email and name from user objects or arrays:

```php
// Works with objects (properties or methods)
$user = new User(['email' => 'jane@example.com', 'name' => 'Jane Smith']);
$avatar = Avatar::for($user)
    ->try('initials')
    ->size(300)
    ->generate();

// Works with arrays
$userData = ['email' => 'bob@example.com', 'name' => 'Bob Wilson'];
$avatar = Avatar::for($userData)
    ->try('gravatar')
    ->fallbackTo('initials')
    ->generate();
```

### Fallback Chain

Create a robust avatar system with automatic fallbacks:

```php
$avatar = Avatar::engine('gravatar')
    ->fallbackTo('dicebear')
    ->fallbackTo('initials')  // Final fallback always succeeds
    ->seed('user@example.com')
    ->name('User Name')
    ->size(256)
    ->generate();
```

## Available Engines

| Engine             | Description                                      | Network Required |
|--------------------|--------------------------------------------------|------------------|
| `initials`         | SVG avatars with user initials in colored shapes | No               |
| `gravatar`         | Fetches from Gravatar.com based on email hash    | Yes              |
| `dicebear`         | Fetches from DiceBear API (various styles)       | Yes              |
| `pixel`            | Generates retro pixel-art style avatars          | No               |
| `multicolor-pixel` | Multi-colored variant of pixel engine            | No               |
| `bauhaus`          | Bauhaus-inspired geometric art avatars           | No               |
| `gradient`         | Beautiful gradient-based avatars                 | No               |

## Output Formats

### HTML Image Tag

```php
$avatar->toHtml();
// <img src="data:image/svg+xml;base64,..." alt="Avatar">
```

### Base64 String

```php
$avatar->toBase64();
// data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ...
```

### URL or Raw Content

```php
$avatar->toUrl();
// For Gravatar/DiceBear: https://www.gravatar.com/avatar/...
// For local engines: Returns base64 data URI
```

### HTTP Response (Laravel/Symfony)

```php
// In a Laravel controller
public function avatar(Request $request)
{
    return Avatar::for($request->user())
        ->try('gravatar')
        ->fallbackTo('initials')
        ->toResponse();
}
```

### Direct String Conversion

```php
echo $avatar; // Automatically converts to HTML img tag
```

## Configuration Options

### Universal Options

These options work across all engines:

```php
Avatar::engine('initials')
    ->seed('unique-identifier')      // Seed for deterministic generation
    ->name('John Doe')                // User's display name
    ->size(200)                       // Avatar size in pixels (default: 200)
    ->generate();
```

### Initials Engine

```php
Avatar::engine('initials')
    ->name('John Doe')
    ->size(256)
    ->shape('circle')                 // 'circle' or 'square'
    ->bold(true)                      // Bold font weight
    ->fontSize(100)                   // Custom font size
    ->backgroundColor(['#FF6B6B', '#4ECDC4', '#45B7D1'])  // Color palette
    ->generate();
```

### Gravatar Engine

```php
Avatar::engine('gravatar')
    ->seed('user@example.com')        // Email address
    ->size(256)
    ->defaultImage('identicon')       // '404', 'mp', 'identicon', 'monsterid', 'wavatar', 'retro', 'robohash', 'blank'
    ->rating('g')                     // 'g', 'pg', 'r', 'x'
    ->generate();
```

### DiceBear Engine

```php
Avatar::engine('dicebear')
    ->seed('user@example.com')
    ->size(256)
    ->style('avataaars')              // DiceBear style (e.g., 'avataaars', 'bottts', 'initials')
    ->backgroundColor('#FF6B6B')      // Hex color
    ->radius(10)                      // Border radius
    ->generate();
```

### Pixel Engines

```php
// Single color pixel
Avatar::engine('pixel')
    ->seed('user@example.com')
    ->size(256)
    ->pixels(5)                       // Grid size (5x5 pixels)
    ->symmetry(true)                  // Mirror horizontally
    ->foregroundLightness(0.4)        // Lightness of colored pixels (0.0-1.0)
    ->backgroundLightness(0.9)        // Lightness of background (0.0-1.0)
    ->generate();

// Multi-color pixel
Avatar::engine('multicolor-pixel')
    ->seed('user@example.com')
    ->size(256)
    ->pixels(6)
    ->symmetry(true)
    ->numColors(4)                    // Number of colors in the palette
    ->backgroundLightness(0.95)
    ->generate();
```

### Bauhaus Engine

```php
Avatar::engine('bauhaus')
    ->seed('user@example.com')
    ->size(256)
    ->numShapes(5)                    // Number of geometric shapes
    ->numColors(3)                    // Color palette size
    ->fillAll(false)                  // Whether to fill entire canvas
    ->generate();
```

### Gradient Engine

```php
Avatar::engine('gradient')
    ->seed('user@example.com')
    ->size(256)
    ->gradientType('linear')          // 'linear' or 'radial'
    ->colorStops(3)                   // Number of color stops
    ->generate();
```

## Advanced Examples

### Laravel Integration

```php
// routes/web.php
Route::get('/avatar/{user}', function (User $user) {
    return Avatar::for($user)
        ->try('gravatar')
        ->fallbackTo('initials')
        ->size(400)
        ->toResponse();
});

// In a Blade template
<img src="{{ route('avatar', $user) }}" alt="{{ $user->name }}">
```

### Custom Styling with Fallback

```php
$avatar = Avatar::for($user)
    ->try('gravatar')
    ->fallbackTo('dicebear')
    ->fallbackTo('initials')
    ->size(300)
    ->defaultImage('404')             // Gravatar: fail if not found
    ->style('avataaars')              // DiceBear: cartoon style
    ->shape('circle')                 // Initials: circular shape
    ->bold(true)                      // Initials: bold text
    ->generate();
```

### Batch Generation

```php
$users = User::all();
$avatars = [];

foreach ($users as $user) {
    $avatars[$user->id] = Avatar::for($user)
        ->try('initials')
        ->size(150)
        ->generate()
        ->toBase64();
}
```

### Dynamic Engine Selection

```php
function generateAvatar(array $user, string $preferredEngine = 'initials'): string
{
    return Avatar::for($user)
        ->try($preferredEngine)
        ->fallbackTo('initials')
        ->size(200)
        ->generate()
        ->toHtml();
}

// Usage
echo generateAvatar($user, 'pixel');
echo generateAvatar($adminUser, 'gravatar');
```

## Testing

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

## Code Quality

This package maintains high code quality standards:

- **PHPStan Level Max**: Strictest static analysis
- **PSR-12**: Code style via Laravel Pint
- **Rector**: Automated refactoring and modernization
- **100% Strict Types**: All files use `declare(strict_types=1)`

Fix code style issues:

```bash
composer lint            # Auto-fix with Pint
composer refacto         # Apply Rector rules
```

## Architecture

### Core Components

- **Avatar**: Static factory providing entry points (`engine()`, `for()`)
- **AvatarBuilder**: Fluent API for configuration and generation
- **GeneratedAvatar**: Output wrapper with multiple format methods
- **EngineInterface**: Contract for all avatar engines
- **AbstractEngine**: Base implementation with shared functionality

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

## Contributing

Contributions are welcome! Please ensure:

1. All tests pass: `composer test`
2. Code follows PSR-12: `composer lint`
3. PHPStan level max passes: `composer test:types`
4. Use strict types in all new files

## License

This package is open-source software licensed under the [MIT license](LICENSE).

## Credits

Created by [renfordt](https://github.com/renfordt)

Built with:
- [meyfa/php-svg](https://github.com/meyfa/php-svg) - SVG generation
- [renfordt/colors](https://github.com/renfordt/colors) - Color manipulation
- [renfordt/clamp](https://github.com/renfordt/clamp) - Value clamping

---

**Forge beautiful avatars with AvatarSmithy.**
