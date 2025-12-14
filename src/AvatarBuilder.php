<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy;

use Renfordt\AvatarSmithy\Exceptions\AllEnginesFailedException;
use Renfordt\AvatarSmithy\Exceptions\EngineFailedException;
use Renfordt\AvatarSmithy\Exceptions\ValidationException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Renfordt\AvatarSmithy\Engines\BauhausEngine;
use Renfordt\AvatarSmithy\Engines\DiceBearEngine;
use Renfordt\AvatarSmithy\Engines\EngineInterface;
use Renfordt\AvatarSmithy\Engines\GradientEngine;
use Renfordt\AvatarSmithy\Engines\GravatarEngine;
use Renfordt\AvatarSmithy\Engines\InitialsEngine;
use Renfordt\AvatarSmithy\Engines\MultiColorPixelEngine;
use Renfordt\AvatarSmithy\Engines\PixelEngine;
use RuntimeException;
use Throwable;

class AvatarBuilder
{
    protected ?EngineInterface $primaryEngine = null;

    /** @var array<EngineInterface> */
    protected array $fallbackEngines = [];

    /** @var array<string, mixed> */
    protected array $options = [];

    protected ?string $seed = null;

    protected ?string $name = null;

    protected int $size = 200;

    /** @var array<int, array{engine: string, error: string, exception: ?Throwable}> */
    protected array $lastErrors = [];

    protected bool $debugMode = false;

    public function __construct(?string $engine = null, protected LoggerInterface $logger = new NullLogger())
    {
        if ($engine !== null) {
            $this->primaryEngine = $this->createEngine($engine);
        }
    }

    public function try(string $engine): self
    {
        if (! $this->primaryEngine instanceof \Renfordt\AvatarSmithy\Engines\EngineInterface) {
            $this->primaryEngine = $this->createEngine($engine);
        } else {
            $this->fallbackEngines[] = $this->createEngine($engine);
        }

        return $this;
    }

    public function fallbackTo(string $engine): self
    {
        $this->fallbackEngines[] = $this->createEngine($engine);

        return $this;
    }

    public function seed(string $seed): self
    {
        $this->seed = $seed;

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function size(int $size): self
    {
        if ($size < 8 || $size > 2048) {
            throw ValidationException::invalidSize($size);
        }

        $this->size = $size;

        return $this;
    }

    public function style(string $style): self
    {
        $this->options['style'] = $style;

        return $this;
    }

    public function variant(string $variant): self
    {
        $this->options['variant'] = $variant;

        return $this;
    }

    /**
     * @param array<string>|string $color
     */
    public function backgroundColor(array|string $color): self
    {
        $this->options['backgroundColor'] = $color;

        return $this;
    }

    public function background(string $color): self
    {
        $this->options['background'] = $color;

        return $this;
    }

    public function color(string $color): self
    {
        $this->options['color'] = $color;

        return $this;
    }

    public function radius(int $radius): self
    {
        if ($radius < 0) {
            throw ValidationException::invalidNonNegativeInteger($radius, 'radius');
        }

        $this->options['radius'] = $radius;

        return $this;
    }

    public function bold(bool $bold = true): self
    {
        $this->options['bold'] = $bold;

        return $this;
    }

    public function defaultImage(string $default): self
    {
        $this->options['default'] = $default;

        return $this;
    }

    public function rating(string $rating): self
    {
        $this->options['rating'] = $rating;

        return $this;
    }

    public function fontSize(int $size): self
    {
        if ($size <= 0) {
            throw ValidationException::invalidPositiveInteger($size, 'fontSize');
        }

        $this->options['fontSize'] = $size;

        return $this;
    }

    public function fontWeight(string $weight): self
    {
        $this->options['fontWeight'] = $weight;

        return $this;
    }

    public function shape(string $shape): self
    {
        $this->options['shape'] = $shape;

        return $this;
    }

    public function pixels(int $pixels): self
    {
        if ($pixels <= 0) {
            throw ValidationException::invalidPositiveInteger($pixels, 'pixels');
        }

        $this->options['pixels'] = $pixels;

        return $this;
    }

    public function symmetry(bool $symmetry = true): self
    {
        $this->options['symmetry'] = $symmetry;

        return $this;
    }

    public function foregroundLightness(float $lightness): self
    {
        if ($lightness < 0.0 || $lightness > 1.0) {
            throw ValidationException::invalidLightness($lightness, 'foregroundLightness');
        }

        $this->options['foregroundLightness'] = $lightness;

        return $this;
    }

    public function backgroundLightness(float $lightness): self
    {
        if ($lightness < 0.0 || $lightness > 1.0) {
            throw ValidationException::invalidLightness($lightness, 'backgroundLightness');
        }

        $this->options['backgroundLightness'] = $lightness;

        return $this;
    }

    public function gradientType(string $type): self
    {
        $this->options['gradientType'] = $type;

        return $this;
    }

    public function numColors(int $count): self
    {
        if ($count <= 0) {
            throw ValidationException::invalidPositiveInteger($count, 'numColors');
        }

        $this->options['numColors'] = $count;

        return $this;
    }

    public function numShapes(int $count): self
    {
        if ($count <= 0) {
            throw ValidationException::invalidPositiveInteger($count, 'numShapes');
        }

        $this->options['numShapes'] = $count;

        return $this;
    }

    public function colorStops(int $count): self
    {
        if ($count <= 0) {
            throw ValidationException::invalidPositiveInteger($count, 'colorStops');
        }

        $this->options['colorStops'] = $count;

        return $this;
    }

    public function fillAll(bool $fillAll = true): self
    {
        $this->options['fillAll'] = $fillAll;

        return $this;
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function debugMode(bool $enabled = true): self
    {
        $this->debugMode = $enabled;

        return $this;
    }

    /**
     * Get the last errors that occurred during avatar generation.
     *
     * @return array<int, array{engine: string, error: string, exception: ?Throwable}>
     */
    public function getLastErrors(): array
    {
        return $this->lastErrors;
    }

    public function generate(): GeneratedAvatar
    {
        if (! $this->primaryEngine instanceof \Renfordt\AvatarSmithy\Engines\EngineInterface) {
            throw new RuntimeException('No engine specified. Use engine() or try() to set an engine.');
        }

        // Clear previous errors
        $this->lastErrors = [];

        $engines = array_merge([$this->primaryEngine], $this->fallbackEngines);

        foreach ($engines as $engine) {
            $engineName = $engine::class;

            try {
                $result = $engine->generate(
                    $this->seed ?? $this->name ?? '',
                    $this->name,
                    $this->size,
                    $this->options
                );

                if ($result !== null) {
                    return new GeneratedAvatar($result, $engine->getContentType(), $this->name, $this->size);
                }

                // Engine returned null (fallback signal)
                $error = 'Engine returned null (no avatar generated)';
                $this->lastErrors[] = [
                    'engine' => $engineName,
                    'error' => $error,
                    'exception' => null,
                ];

                $this->logger->debug("Avatar engine '{$engineName}' returned null", [
                    'engine' => $engineName,
                ]);
            } catch (Throwable $exception) {
                $error = $exception->getMessage();

                $this->lastErrors[] = [
                    'engine' => $engineName,
                    'error' => $error,
                    'exception' => $exception,
                ];

                $this->logger->warning("Avatar engine '{$engineName}' failed: {$error}", [
                    'engine' => $engineName,
                    'exception' => $exception,
                ]);

                // In debug mode, rethrow the exception immediately
                if ($this->debugMode) {
                    throw new EngineFailedException($engineName, $error, 0, $exception);
                }

                continue;
            }
        }

        throw new AllEnginesFailedException($this->lastErrors);
    }

    public function toResponse(): mixed
    {
        return $this->generate()->toResponse();
    }

    protected function createEngine(string $engine): EngineInterface
    {
        return match (strtolower($engine)) {
            'bauhaus' => new BauhausEngine(),
            'dicebear' => new DiceBearEngine(),
            'gradient' => new GradientEngine(),
            'gravatar' => new GravatarEngine(),
            'initials' => new InitialsEngine(),
            'multicolor-pixel' => new MultiColorPixelEngine(),
            'pixel' => new PixelEngine(),
            default => throw new RuntimeException("Unknown engine: {$engine}"),
        };
    }
}
