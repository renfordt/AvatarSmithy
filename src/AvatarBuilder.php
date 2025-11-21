<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy;

use Renfordt\AvatarSmithy\Engines\BauhausEngine;
use Renfordt\AvatarSmithy\Engines\DiceBearEngine;
use Renfordt\AvatarSmithy\Engines\EngineInterface;
use Renfordt\AvatarSmithy\Engines\GradientEngine;
use Renfordt\AvatarSmithy\Engines\GravatarEngine;
use Renfordt\AvatarSmithy\Engines\InitialsEngine;
use Renfordt\AvatarSmithy\Engines\MultiColorPixelEngine;
use Renfordt\AvatarSmithy\Engines\PixelEngine;
use RuntimeException;

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

    public function __construct(?string $engine = null)
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
        $this->options['foregroundLightness'] = $lightness;

        return $this;
    }

    public function backgroundLightness(float $lightness): self
    {
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
        $this->options['numColors'] = $count;

        return $this;
    }

    public function numShapes(int $count): self
    {
        $this->options['numShapes'] = $count;

        return $this;
    }

    public function colorStops(int $count): self
    {
        $this->options['colorStops'] = $count;

        return $this;
    }

    public function fillAll(bool $fillAll = true): self
    {
        $this->options['fillAll'] = $fillAll;

        return $this;
    }

    public function generate(): GeneratedAvatar
    {
        if (! $this->primaryEngine instanceof \Renfordt\AvatarSmithy\Engines\EngineInterface) {
            throw new RuntimeException('No engine specified. Use engine() or try() to set an engine.');
        }

        $engines = array_merge([$this->primaryEngine], $this->fallbackEngines);

        foreach ($engines as $engine) {
            try {
                $result = $engine->generate(
                    $this->seed ?? $this->name ?? '',
                    $this->name,
                    $this->size,
                    $this->options
                );

                if ($result !== null) {
                    return new GeneratedAvatar($result, $engine->getContentType());
                }
            } catch (\Exception) {
                continue;
            }
        }

        throw new RuntimeException('All avatar engines failed to generate an avatar.');
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
