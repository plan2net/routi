<?php

declare(strict_types=1);

namespace Plan2net\Routi\Tests\Unit\Routing\Aspect;

use InvalidArgumentException;
use LengthException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Plan2net\Routi\Routing\Aspect\StaticPaddedRangeMapper;

class StaticPaddedRangeMapperTest extends TestCase
{
    #[Test]
    public function constructorWithLeftPadding(): void
    {
        $mapper = new StaticPaddedRangeMapper([
            'start' => '1',
            'end' => '12',
            'padString' => '0',
            'padLength' => 2,
            'padType' => STR_PAD_LEFT
        ]);

        $this->assertInstanceOf(StaticPaddedRangeMapper::class, $mapper);
        $this->assertEquals('01', $mapper->generate('01'));
        $this->assertEquals('12', $mapper->generate('12'));
    }

    #[Test]
    public function constructorWithRightPadding(): void
    {
        $mapper = new StaticPaddedRangeMapper([
            'start' => '1',
            'end' => '5',
            'padString' => '_',
            'padLength' => 3,
            'padType' => STR_PAD_RIGHT
        ]);

        $this->assertEquals('1__', $mapper->generate('1__'));
        $this->assertEquals('5__', $mapper->generate('5__'));
    }

    #[Test]
    public function constructorWithBothPadding(): void
    {
        $mapper = new StaticPaddedRangeMapper([
            'start' => '1',
            'end' => '3',
            'padString' => '*',
            'padLength' => 5,
            'padType' => STR_PAD_BOTH
        ]);

        $this->assertEquals('**1**', $mapper->generate('**1**'));
        $this->assertEquals('**2**', $mapper->generate('**2**'));
        $this->assertEquals('**3**', $mapper->generate('**3**'));
    }

    #[Test]
    public function constructorThrowsExceptionWhenStartIsNotString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('start must be string');
        $this->expectExceptionCode(1537277163);

        new StaticPaddedRangeMapper([
            'start' => 1,
            'end' => '10',
            'padString' => '0',
            'padLength' => 2,
            'padType' => STR_PAD_LEFT
        ]);
    }

    #[Test]
    public function constructorThrowsExceptionWhenEndIsNotString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('end must be string');
        $this->expectExceptionCode(1537277164);

        new StaticPaddedRangeMapper([
            'start' => '1',
            'end' => 10,
            'padString' => '0',
            'padLength' => 2,
            'padType' => STR_PAD_LEFT
        ]);
    }

    #[Test]
    public function constructorThrowsExceptionWhenPadStringIsNotString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('padString must be string');
        $this->expectExceptionCode(1538277165);

        new StaticPaddedRangeMapper([
            'start' => '1',
            'end' => '10',
            'padString' => null,
            'padLength' => 2,
            'padType' => STR_PAD_LEFT
        ]);
    }

    #[Test]
    public function constructorThrowsExceptionWhenPadLengthIsNotInteger(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('padLength must be integer');
        $this->expectExceptionCode(1538277166);

        new StaticPaddedRangeMapper([
            'start' => '1',
            'end' => '10',
            'padString' => '0',
            'padLength' => '2',
            'padType' => STR_PAD_LEFT
        ]);
    }

    #[Test]
    public function constructorThrowsExceptionWhenPadTypeIsNotInteger(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('padType must be integer');
        $this->expectExceptionCode(1538277167);

        new StaticPaddedRangeMapper([
            'start' => '1',
            'end' => '10',
            'padString' => '0',
            'padLength' => 2,
            'padType' => '0'
        ]);
    }

    #[Test]
    public function constructorThrowsExceptionWhenPadTypeIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('padType must be valid');
        $this->expectExceptionCode(1538277168);

        new StaticPaddedRangeMapper([
            'start' => '1',
            'end' => '10',
            'padString' => '0',
            'padLength' => 2,
            'padType' => 999
        ]);
    }

    #[Test]
    public function constructorThrowsExceptionWhenRangeIsTooLarge(): void
    {
        $this->expectException(LengthException::class);
        $this->expectExceptionMessage('Range is larger than 1000 items');
        $this->expectExceptionCode(1537696771);

        new StaticPaddedRangeMapper([
            'start' => '1',
            'end' => '1001',
            'padString' => '0',
            'padLength' => 4,
            'padType' => STR_PAD_LEFT
        ]);
    }

    #[Test]
    public function generateWithMonthsExample(): void
    {
        // This matches the example in the class documentation
        $mapper = new StaticPaddedRangeMapper([
            'start' => '1',
            'end' => '12',
            'padString' => '0',
            'padLength' => 2,
            'padType' => STR_PAD_LEFT
        ]);

        $this->assertEquals('01', $mapper->generate('01'));
        $this->assertEquals('02', $mapper->generate('02'));
        $this->assertEquals('09', $mapper->generate('09'));
        $this->assertEquals('10', $mapper->generate('10'));
        $this->assertEquals('11', $mapper->generate('11'));
        $this->assertEquals('12', $mapper->generate('12'));
        $this->assertNull($mapper->generate('13'));
        $this->assertNull($mapper->generate('00'));
    }

    #[Test]
    public function resolveWithPadding(): void
    {
        $mapper = new StaticPaddedRangeMapper([
            'start' => '5',
            'end' => '10',
            'padString' => '0',
            'padLength' => 3,
            'padType' => STR_PAD_LEFT
        ]);

        $this->assertEquals('005', $mapper->resolve('005'));
        $this->assertEquals('010', $mapper->resolve('010'));
        $this->assertNull($mapper->resolve('004'));
        $this->assertNull($mapper->resolve('011'));
    }

    #[Test]
    public function multiCharacterPadString(): void
    {
        $mapper = new StaticPaddedRangeMapper([
            'start' => '1',
            'end' => '3',
            'padString' => 'XY',
            'padLength' => 6,
            'padType' => STR_PAD_LEFT
        ]);

        $this->assertEquals('XYXYX1', $mapper->generate('XYXYX1'));
        $this->assertEquals('XYXYX2', $mapper->generate('XYXYX2'));
        $this->assertEquals('XYXYX3', $mapper->generate('XYXYX3'));
    }
}