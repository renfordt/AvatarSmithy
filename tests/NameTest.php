<?php

declare(strict_types=1);

namespace Renfordt\AvatarSmithy\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Renfordt\AvatarSmithy\Support\Name;

#[CoversClass(Name::class)]
class NameTest extends TestCase
{
    public function test_constructor(): void
    {
        $this->expectNotToPerformAssertions();
        new Name('John Doe');
    }

    public function test_make_factory_method(): void
    {
        $this->expectNotToPerformAssertions();
        Name::make('Jane Smith');
    }

    public function test_get_name(): void
    {
        $nameString = 'John Doe';
        $name = new Name($nameString);

        $this->assertSame($nameString, $name->getName());
    }

    public function test_get_hash(): void
    {
        $nameString = 'John Doe';
        $name = new Name($nameString);
        $expectedHash = md5($nameString);

        $this->assertSame($expectedHash, $name->getHash());
    }

    public function test_get_initials_single_name(): void
    {
        $name = new Name('John');

        $this->assertSame('J', $name->getInitials());
    }

    public function test_get_initials_two_names(): void
    {
        $name = new Name('John Doe');

        $this->assertSame('JD', $name->getInitials());
    }

    public function test_get_initials_three_names(): void
    {
        $name = new Name('John Middle Doe');

        $this->assertSame('JMD', $name->getInitials());
    }

    public function test_get_initials_with_extra_spaces(): void
    {
        $name = new Name('  John   Doe  ');

        $this->assertSame('JD', $name->getInitials());
    }

    public function test_get_initials_empty_name(): void
    {
        $name = new Name('');

        $this->assertSame('', $name->getInitials());
    }

    public function test_get_initials_whitespace_only(): void
    {
        $name = new Name('   ');

        $this->assertSame('', $name->getInitials());
    }

    public function test_get_initials_lowercase(): void
    {
        $name = new Name('john doe');

        $this->assertSame('JD', $name->getInitials());
    }

    public function test_get_initials_mixed_case(): void
    {
        $name = new Name('jOhN dOe');

        $this->assertSame('JD', $name->getInitials());
    }

    public function test_get_initials_unicode_characters(): void
    {
        $name = new Name('Müller Öztürk');

        $this->assertSame('MÖ', $name->getInitials());
    }

    public function test_get_initials_with_special_characters(): void
    {
        $name = new Name('José María');

        $this->assertSame('JM', $name->getInitials());
    }

    public function test_get_hex_color_default_offset(): void
    {
        $name = new Name('John Doe');
        $color = $name->getHexColor();

        $this->assertMatchesRegularExpression('/^#[0-9a-fA-F]{6}$/', $color->getHexStr());
    }

    public function test_get_hex_color_with_offset(): void
    {
        $name = new Name('John Doe');
        $color = $name->getHexColor(5);

        $this->assertMatchesRegularExpression('/^#[0-9a-fA-F]{6}$/', $color->getHexStr());
    }

    public function test_get_hex_color_different_offsets_return_different_colors(): void
    {
        $name = new Name('John Doe');
        $color1 = $name->getHexColor(0);
        $color2 = $name->getHexColor(5);

        $this->assertNotEquals($color1->getHexStr(), $color2->getHexStr());
    }

    public function test_same_name_produces_same_hash(): void
    {
        $name1 = new Name('John Doe');
        $name2 = new Name('John Doe');

        $this->assertSame($name1->getHash(), $name2->getHash());
    }

    public function test_same_name_produces_same_color(): void
    {
        $name1 = new Name('John Doe');
        $name2 = new Name('John Doe');

        $this->assertEquals($name1->getHexColor()->getHexStr(), $name2->getHexColor()->getHexStr());
    }

    public function test_different_names_produce_different_hashes(): void
    {
        $name1 = new Name('John Doe');
        $name2 = new Name('Jane Smith');

        $this->assertNotSame($name1->getHash(), $name2->getHash());
    }

    public function test_hash_is_32_characters_long(): void
    {
        $name = new Name('John Doe');

        $this->assertSame(32, strlen($name->getHash()));
    }

    public function test_make_with_empty_string(): void
    {
        $name = Name::make('');

        $this->assertSame('', $name->getName());
        $this->assertSame('', $name->getInitials());
    }

    public function test_get_initials_preserves_uppercase(): void
    {
        $name = new Name('JOHN DOE');

        $this->assertSame('JD', $name->getInitials());
    }

    public function test_name_with_numbers(): void
    {
        $name = new Name('John 123');

        $this->assertSame('J1', $name->getInitials());
    }

    public function test_single_character_name(): void
    {
        $name = new Name('A');

        $this->assertSame('A', $name->getInitials());
    }

    public function test_many_name_parts(): void
    {
        $name = new Name('A B C D E F');

        $this->assertSame('ABCDEF', $name->getInitials());
    }

    public function test_hash_consistency_after_multiple_calls(): void
    {
        $name = new Name('John Doe');
        $hash1 = $name->getHash();
        $hash2 = $name->getHash();

        $this->assertSame($hash1, $hash2);
    }

    public function test_color_consistency_after_multiple_calls(): void
    {
        $name = new Name('John Doe');
        $color1 = $name->getHexColor();
        $color2 = $name->getHexColor();

        $this->assertEquals($color1->getHexStr(), $color2->getHexStr());
    }
}
