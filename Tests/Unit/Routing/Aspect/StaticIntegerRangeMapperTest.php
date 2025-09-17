<?php

declare(strict_types=1);

namespace Plan2net\Routi\Tests\Unit\Routing\Aspect;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Plan2net\Routi\Routing\Aspect\StaticIntegerRangeMapper;

class StaticIntegerRangeMapperTest extends TestCase
{
    #[Test]
    public function constructorWithValidSettings(): void
    {
        $mapper = new StaticIntegerRangeMapper([
            'start' => '1',
            'end' => '10'
        ]);

        $this->assertInstanceOf(StaticIntegerRangeMapper::class, $mapper);
        $this->assertEquals(9, $mapper->count());
    }

    #[Test]
    public function constructorThrowsExceptionWhenStartIsNotNumeric(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('start must be a number');
        $this->expectExceptionCode(1577297576);

        new StaticIntegerRangeMapper([
            'start' => 'not-a-number',
            'end' => '10'
        ]);
    }

    #[Test]
    public function constructorThrowsExceptionWhenEndIsNotNumeric(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('end must be a number');
        $this->expectExceptionCode(1577297577);

        new StaticIntegerRangeMapper([
            'start' => '1',
            'end' => 'not-a-number'
        ]);
    }

    #[Test]
    public function generateReturnsValueWithinRange(): void
    {
        $mapper = new StaticIntegerRangeMapper([
            'start' => '5',
            'end' => '15'
        ]);

        $this->assertEquals('5', $mapper->generate('5'));
        $this->assertEquals('10', $mapper->generate('10'));
        $this->assertEquals('15', $mapper->generate('15'));
    }

    #[Test]
    public function generateReturnsNullForValueOutsideRange(): void
    {
        $mapper = new StaticIntegerRangeMapper([
            'start' => '5',
            'end' => '15'
        ]);

        $this->assertNull($mapper->generate('4'));
        $this->assertNull($mapper->generate('16'));
        $this->assertNull($mapper->generate('100'));
    }

    #[Test]
    public function resolveReturnsValueWithinRange(): void
    {
        $mapper = new StaticIntegerRangeMapper([
            'start' => '10',
            'end' => '20'
        ]);

        $this->assertEquals('10', $mapper->resolve('10'));
        $this->assertEquals('15', $mapper->resolve('15'));
        $this->assertEquals('20', $mapper->resolve('20'));
    }

    #[Test]
    public function resolveReturnsNullForValueOutsideRange(): void
    {
        $mapper = new StaticIntegerRangeMapper([
            'start' => '10',
            'end' => '20'
        ]);

        $this->assertNull($mapper->resolve('9'));
        $this->assertNull($mapper->resolve('21'));
        $this->assertNull($mapper->resolve('0'));
    }

    #[Test]
    public function countReturnsCorrectRangeDifference(): void
    {
        $mapper1 = new StaticIntegerRangeMapper([
            'start' => '0',
            'end' => '10'
        ]);
        $this->assertEquals(10, $mapper1->count());

        $mapper2 = new StaticIntegerRangeMapper([
            'start' => '5',
            'end' => '5'
        ]);
        $this->assertEquals(0, $mapper2->count());

        $mapper3 = new StaticIntegerRangeMapper([
            'start' => '-5',
            'end' => '5'
        ]);
        $this->assertEquals(10, $mapper3->count());
    }

    #[Test]
    public function negativeRangeSupport(): void
    {
        $mapper = new StaticIntegerRangeMapper([
            'start' => '-10',
            'end' => '-5'
        ]);

        $this->assertEquals('-7', $mapper->generate('-7'));
        $this->assertEquals('-7', $mapper->resolve('-7'));
        $this->assertNull($mapper->generate('-11'));
        $this->assertNull($mapper->generate('-4'));
    }
}