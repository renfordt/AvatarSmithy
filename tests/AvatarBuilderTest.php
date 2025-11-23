<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Renfordt\AvatarSmithy\AvatarBuilder;
use Renfordt\AvatarSmithy\Engines\AbstractEngine;
use Renfordt\AvatarSmithy\Engines\BauhausEngine;
use Renfordt\AvatarSmithy\Engines\DiceBearEngine;
use Renfordt\AvatarSmithy\Engines\GradientEngine;
use Renfordt\AvatarSmithy\Engines\GravatarEngine;
use Renfordt\AvatarSmithy\Engines\InitialsEngine;
use Renfordt\AvatarSmithy\Engines\MultiColorPixelEngine;
use Renfordt\AvatarSmithy\Engines\PixelEngine;
use Renfordt\AvatarSmithy\GeneratedAvatar;
use Renfordt\AvatarSmithy\Support\Name;
use RuntimeException;

#[CoversClass(AvatarBuilder::class)]
#[CoversClass(AbstractEngine::class)]
#[CoversClass(BauhausEngine::class)]
#[CoversClass(DiceBearEngine::class)]
#[CoversClass(GradientEngine::class)]
#[CoversClass(GravatarEngine::class)]
#[CoversClass(InitialsEngine::class)]
#[CoversClass(MultiColorPixelEngine::class)]
#[CoversClass(PixelEngine::class)]
#[CoversClass(GeneratedAvatar::class)]
#[CoversClass(Name::class)]
class AvatarBuilderTest extends TestCase
{
    public function test_constructor_without_engine(): void
    {
        $this->expectNotToPerformAssertions();
        new AvatarBuilder();
    }

    public function test_constructor_with_engine(): void
    {
        $this->expectNotToPerformAssertions();
        new AvatarBuilder('initials');
    }

    public function test_try_method_sets_primary_engine_when_none_exists(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->try('initials');

        $this->assertSame($builder, $result);
    }

    public function test_try_method_adds_fallback_when_primary_exists(): void
    {
        $builder = new AvatarBuilder('initials');
        $result = $builder->try('gravatar');

        $this->assertSame($builder, $result);
    }

    public function test_fallback_to_method(): void
    {
        $builder = new AvatarBuilder('initials');
        $result = $builder->fallbackTo('gravatar');

        $this->assertSame($builder, $result);
    }

    public function test_seed_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->seed('test@example.com');

        $this->assertSame($builder, $result);
    }

    public function test_name_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->name('John Doe');

        $this->assertSame($builder, $result);
    }

    public function test_size_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->size(300);

        $this->assertSame($builder, $result);
    }

    public function test_style_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->style('rounded');

        $this->assertSame($builder, $result);
    }

    public function test_variant_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->variant('beam');

        $this->assertSame($builder, $result);
    }

    public function test_background_color_method_with_string(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->backgroundColor('#ff0000');

        $this->assertSame($builder, $result);
    }

    public function test_background_color_method_with_array(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->backgroundColor(['#ff0000', '#00ff00']);

        $this->assertSame($builder, $result);
    }

    public function test_background_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->background('#00ff00');

        $this->assertSame($builder, $result);
    }

    public function test_color_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->color('#0000ff');

        $this->assertSame($builder, $result);
    }

    public function test_radius_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->radius(10);

        $this->assertSame($builder, $result);
    }

    public function test_bold_method_with_default(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->bold();

        $this->assertSame($builder, $result);
    }

    public function test_bold_method_with_false(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->bold(false);

        $this->assertSame($builder, $result);
    }

    public function test_default_image_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->defaultImage('identicon');

        $this->assertSame($builder, $result);
    }

    public function test_rating_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->rating('pg');

        $this->assertSame($builder, $result);
    }

    public function test_font_size_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->fontSize(24);

        $this->assertSame($builder, $result);
    }

    public function test_font_weight_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->fontWeight('bold');

        $this->assertSame($builder, $result);
    }

    public function test_shape_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->shape('circle');

        $this->assertSame($builder, $result);
    }

    public function test_pixels_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->pixels(8);

        $this->assertSame($builder, $result);
    }

    public function test_symmetry_method_with_default(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->symmetry();

        $this->assertSame($builder, $result);
    }

    public function test_symmetry_method_with_false(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->symmetry(false);

        $this->assertSame($builder, $result);
    }

    public function test_foreground_lightness_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->foregroundLightness(0.5);

        $this->assertSame($builder, $result);
    }

    public function test_background_lightness_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->backgroundLightness(0.8);

        $this->assertSame($builder, $result);
    }

    public function test_gradient_type_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->gradientType('linear');

        $this->assertSame($builder, $result);
    }

    public function test_num_colors_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->numColors(5);

        $this->assertSame($builder, $result);
    }

    public function test_num_shapes_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->numShapes(10);

        $this->assertSame($builder, $result);
    }

    public function test_color_stops_method(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->colorStops(3);

        $this->assertSame($builder, $result);
    }

    public function test_fill_all_method_with_default(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->fillAll();

        $this->assertSame($builder, $result);
    }

    public function test_fill_all_method_with_false(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder->fillAll(false);

        $this->assertSame($builder, $result);
    }

    public function test_method_chaining(): void
    {
        $builder = new AvatarBuilder();
        $result = $builder
            ->seed('test@example.com')
            ->name('John Doe')
            ->size(300)
            ->color('#ff0000');

        $this->assertSame($builder, $result);
    }

    public function test_generate_without_engine_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No engine specified');

        $builder = new AvatarBuilder();
        $builder->generate();
    }

    public function test_generate_with_engine_returns_generated_avatar(): void
    {
        $this->expectNotToPerformAssertions();
        $builder = new AvatarBuilder('initials');
        $builder->name('John Doe')->generate();
    }

    public function test_generate_with_try_method(): void
    {
        $this->expectNotToPerformAssertions();
        $builder = new AvatarBuilder();
        $builder->try('initials')->name('Jane Doe')->generate();
    }

    public function test_create_engine_throws_exception_for_unknown_engine(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown engine');

        new AvatarBuilder('unknown-engine');
    }

    public function test_create_engine_for_bauhaus(): void
    {
        $this->expectNotToPerformAssertions();
        new AvatarBuilder('bauhaus');
    }

    public function test_create_engine_for_dicebear(): void
    {
        $this->expectNotToPerformAssertions();
        new AvatarBuilder('dicebear');
    }

    public function test_create_engine_for_gradient(): void
    {
        $this->expectNotToPerformAssertions();
        new AvatarBuilder('gradient');
    }

    public function test_create_engine_for_gravatar(): void
    {
        $this->expectNotToPerformAssertions();
        new AvatarBuilder('gravatar');
    }

    public function test_create_engine_for_initials(): void
    {
        $this->expectNotToPerformAssertions();
        new AvatarBuilder('initials');
    }

    public function test_create_engine_for_multicolor_pixel(): void
    {
        $this->expectNotToPerformAssertions();
        new AvatarBuilder('multicolor-pixel');
    }

    public function test_create_engine_for_pixel(): void
    {
        $this->expectNotToPerformAssertions();
        new AvatarBuilder('pixel');
    }

    public function test_create_engine_case_insensitive(): void
    {
        $this->expectNotToPerformAssertions();
        new AvatarBuilder('INITIALS');
    }
}
