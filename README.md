<div align="center">

![Preview Image](preview.png "AvatarSmithy Preview")

# AvatarSmithy

**Forge beautiful avatars with ease.**

[![PHP Version](https://img.shields.io/badge/PHP-8.4+-777BB4?style=flat-square&logo=php)](https://www.php.net)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat-square)](CONTRIBUTING.md)

A modern PHP library that bundles **7 avatar generation engines** into a unified, fluent API.
Create stunning avatars from user data with automatic fallback support and extensive customization.

[Installation](#installation) • [Quick Start](#quick-start) • [Engines](#available-engines) • [Examples](#advanced-examples) • [Contributing](CONTRIBUTING.md)

</div>

---

## ✨ Features

- 🎨 **7 Built-in Engines** — From SVG initials to pixel art, Gravatar integration, and more
- 🔗 **Fluent API** — Intuitive builder pattern for effortless configuration
- 🛡️ **Smart Fallback** — Automatic failover between engines ensures reliability
- 📦 **Flexible Output** — Export as HTML, Base64, URLs, PSR-7 responses, or stream directly
- ⚙️ **Highly Customizable** — Extensive styling options for each engine
- 🚀 **Framework Agnostic** — Works standalone or integrates seamlessly with Laravel/Symfony
- 🔒 **Type Safe** — Built with strict types and PHPStan level max
- ⚡ **Modern PHP** — Requires PHP 8.4+ with latest language features

## 📋 Requirements

- PHP 8.4 or higher
- GD extension
- Composer

## 📦 Installation

Install via Composer:

```bash
composer require renfordt/avatar-smithy
```

## 🚀 Quick Start

### Basic Usage

Generate an avatar with just a few lines:

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
// Works with objects
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

## 🎨 Available Engines

| Engine                | Description                                      | Network | Example Use Case                |
|-----------------------|--------------------------------------------------|---------|---------------------------------|
| `initials`            | SVG avatars with user initials in colored shapes | ❌       | Default user profiles           |
| `gravatar`            | Fetches from Gravatar.com based on email hash    | ✅       | Blog comments, social platforms |
| `dicebear`            | Fetches from DiceBear API (various styles)       | ✅       | Fun, cartoon-style avatars      |
| `pixel`               | Generates retro pixel-art style avatars          | ❌       | Gaming platforms, retro themes  |
| `multicolor-pixel`    | Multi-colored variant of pixel engine            | ❌       | Vibrant pixel art avatars       |
| `bauhaus`             | Bauhaus-inspired geometric art avatars           | ❌       | Modern, artistic profiles       |
| `gradient`            | Beautiful gradient-based avatars                 | ❌       | Minimalist, colorful designs    |

> **💡 Tip:** Engines marked with ❌ generate avatars locally without network requests, while ✅ engines fetch from external APIs.

## 📤 Output Formats

AvatarSmithy provides multiple output formats for maximum flexibility:

<table>
<tr>
<td width="50%">

### HTML Image Tag

```php
$avatar->toHtml();
// <img src="data:image/svg+xml;base64,..."
//      alt="Avatar">
```

</td>
<td width="50%">

### Base64 Data URI

```php
$avatar->toBase64();
// data:image/svg+xml;base64,
// PHN2ZyB3aWR0aD0iMjAwIi...
```

</td>
</tr>
<tr>
<td>

### URL or Raw Content

```php
$avatar->toUrl();
// Gravatar: https://gravatar.com/...
// Local: data:image/svg+xml;base64,...
```

</td>
<td>

### PSR-7 HTTP Response

```php
// In a Laravel/Symfony controller
return Avatar::for($user)
    ->try('gravatar')
    ->fallbackTo('initials')
    ->toResponse();
```

</td>
</tr>
<tr>
<td>

### Direct String Conversion

```php
echo $avatar;
// Auto-converts to HTML img tag
```

</td>
<td>

### Save to File

```php
$avatar->save('/path/to/avatar.svg');
// Saves avatar to filesystem
```

</td>
</tr>
</table>

## ⚙️ Configuration Options

### Universal Options

These options work across all engines:

```php
Avatar::engine('initials')
    ->seed('unique-identifier')      // Seed for deterministic generation
    ->name('John Doe')                // User's display name
    ->size(200)                       // Avatar size in pixels (default: 200)
    ->generate();
```

### Engine-Specific Options

<details>
<summary><strong>Initials Engine</strong></summary>

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
</details>

<details>
<summary><strong>Gravatar Engine</strong></summary>

```php
Avatar::engine('gravatar')
    ->seed('user@example.com')        // Email address
    ->size(256)
    ->defaultImage('identicon')       // '404', 'mp', 'identicon', 'monsterid',
                                      // 'wavatar', 'retro', 'robohash', 'blank'
    ->rating('g')                     // 'g', 'pg', 'r', 'x'
    ->generate();
```
</details>

<details>
<summary><strong>DiceBear Engine</strong></summary>

```php
Avatar::engine('dicebear')
    ->seed('user@example.com')
    ->size(256)
    ->style('avataaars')              // DiceBear style (e.g., 'avataaars', 'bottts')
    ->backgroundColor('#FF6B6B')      // Hex color
    ->radius(10)                      // Border radius
    ->generate();
```
</details>

<details>
<summary><strong>Pixel Engines</strong></summary>

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
    ->numColors(4)                    // Number of colors in palette
    ->backgroundLightness(0.95)
    ->generate();
```
</details>

<details>
<summary><strong>Bauhaus Engine</strong></summary>

```php
Avatar::engine('bauhaus')
    ->seed('user@example.com')
    ->size(256)
    ->numShapes(5)                    // Number of geometric shapes
    ->numColors(3)                    // Color palette size
    ->fillAll(false)                  // Whether to fill entire canvas
    ->generate();
```
</details>

<details>
<summary><strong>Gradient Engine</strong></summary>

```php
Avatar::engine('gradient')
    ->seed('user@example.com')
    ->size(256)
    ->gradientType('linear')          // 'linear' or 'radial'
    ->colorStops(3)                   // Number of color stops
    ->generate();
```
</details>

## 💎 Advanced Examples

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

### Custom Styling with Fallback Chain

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

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on:

- Development setup and testing
- Code quality standards
- Architecture overview
- Creating custom engines
- Pull request process

## 📄 License

This package is open-source software licensed under the [MIT license](LICENSE).

## 🙏 Credits

**Created by** [renfordt](https://github.com/renfordt)

**Built with:**
- [meyfa/php-svg](https://github.com/meyfa/php-svg) — SVG generation
- [renfordt/colors](https://github.com/renfordt/colors) — Color manipulation
- [renfordt/clamp](https://github.com/renfordt/clamp) — Value clamping

---

<div align="center">

**⚒️ Forge beautiful avatars with AvatarSmithy ⚒️**

[⬆ Back to Top](#avatarsmithy)

</div>
