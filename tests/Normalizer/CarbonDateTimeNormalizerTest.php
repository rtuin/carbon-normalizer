<?php

namespace Rtuin\Normalizer\Tests;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;
use Rtuin\Normalizer\CarbonDateTimeNormalizer;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

class CarbonDateTimeNormalizerTest extends TestCase
{
    public function test_it_supports_datetimeinterface_normalization(): void
    {
        $normalizer = new CarbonDateTimeNormalizer();
        $this->assertTrue($normalizer->supportsNormalization(new \DateTime()));
        $this->assertTrue($normalizer->supportsNormalization(new \DateTimeImmutable()));
        $this->assertTrue($normalizer->supportsNormalization(new Carbon()));
        $this->assertTrue($normalizer->supportsNormalization(new CarbonImmutable()));

        $this->assertFalse($normalizer->supportsNormalization(new \ArrayObject()));
    }

    public function test_it_normalizes_according_to_default_format(): void
    {
        $normalizer = new CarbonDateTimeNormalizer([CarbonDateTimeNormalizer::TIMEZONE_KEY => 'Europe/Amsterdam']);
        $object = Carbon::createFromFormat(\DateTime::RFC3339, '2014-06-25T08:40:00+02:00');

        $this->assertSame('2014-06-25T08:40:00+02:00', $normalizer->normalize($object));
    }

    public function test_it_normalizes_according_to_overridden_format(): void
    {
        $normalizer = new CarbonDateTimeNormalizer([CarbonDateTimeNormalizer::TIMEZONE_KEY => 'Europe/Amsterdam']);

        $object = Carbon::createFromFormat(\DateTime::RFC3339, '2014-06-25T08:40:00+02:00');

        $this->assertSame('25-06-2014 08:40:00', $normalizer->normalize($object, null, [CarbonDateTimeNormalizer::FORMAT_KEY => 'd-m-Y H:i:s']));
    }

    public function test_it_supports_datetimeinterface_denormalization(): void
    {
        $normalizer = new CarbonDateTimeNormalizer();
        $this->assertTrue($normalizer->supportsDenormalization('2014-06-25T08:40:00+02:00', \DateTime::class));
        $this->assertTrue($normalizer->supportsDenormalization('2014-06-25T08:40:00+02:00', \DateTimeImmutable::class));
        $this->assertTrue($normalizer->supportsDenormalization('2014-06-25T08:40:00+02:00', Carbon::class));
        $this->assertTrue($normalizer->supportsDenormalization('2014-06-25T08:40:00+02:00', CarbonImmutable::class));

        $this->assertFalse($normalizer->supportsDenormalization('2014-06-25T08:40:00+02:00', \ArrayObject::class));
    }

    public function test_it_does_not_denormalize_empty_string(): void
    {
        $normalizer = new CarbonDateTimeNormalizer();

        $this->expectException(NotNormalizableValueException::class);
        $normalizer->denormalize('', CarbonImmutable::class);
    }

    public function test_it_denormalizes_according_to_default_format(): void
    {
        $normalizer = new CarbonDateTimeNormalizer([CarbonDateTimeNormalizer::TIMEZONE_KEY => 'Europe/Amsterdam']);

        // CarbonImmutable
        $result = $normalizer->denormalize('2014-06-25T08:40:00+02:00', CarbonImmutable::class);
        $this->assertInstanceOf(CarbonImmutable::class, $result);
        $this->assertSame('2014-06-25T08:40:00+02:00', $result->format(\DateTime::RFC3339));

        // Carbon
        $result = $normalizer->denormalize('2014-06-25T08:40:00+02:00', Carbon::class);
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertSame('2014-06-25T08:40:00+02:00', $result->format(\DateTime::RFC3339));

        // DateTime
        $result = $normalizer->denormalize('2014-06-25T08:40:00+02:00', \DateTime::class);
        $this->assertInstanceOf(\DateTime::class, $result);
        $this->assertSame('2014-06-25T08:40:00+02:00', $result->format(\DateTime::RFC3339));

        // DateTimeImmutable
        $result = $normalizer->denormalize('2014-06-25T08:40:00+02:00', \DateTimeImmutable::class);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result);
        $this->assertSame('2014-06-25T08:40:00+02:00', $result->format(\DateTimeImmutable::RFC3339));
    }

    public function test_it_denormalizes_according_to_overridden_format(): void
    {
        $normalizer = new CarbonDateTimeNormalizer([CarbonDateTimeNormalizer::TIMEZONE_KEY => 'Europe/Amsterdam']);

        // CarbonImmutable
        $result = $normalizer->denormalize('25-06-2014 08:40:00', CarbonImmutable::class, null, [CarbonDateTimeNormalizer::FORMAT_KEY => 'd-m-Y H:i:s']);
        $this->assertInstanceOf(CarbonImmutable::class, $result);
        $this->assertSame('2014-06-25T08:40:00+02:00', $result->format(\DateTime::RFC3339));

        // Carbon
        $result = $normalizer->denormalize('25-06-2014 08:40:00', Carbon::class, null, [CarbonDateTimeNormalizer::FORMAT_KEY => 'd-m-Y H:i:s']);
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertSame('2014-06-25T08:40:00+02:00', $result->format(\DateTime::RFC3339));

        // DateTime
        $result = $normalizer->denormalize('25-06-2014 08:40:00', \DateTime::class, null, [CarbonDateTimeNormalizer::FORMAT_KEY => 'd-m-Y H:i:s']);
        $this->assertInstanceOf(\DateTime::class, $result);
        $this->assertSame('2014-06-25T08:40:00+02:00', $result->format(\DateTime::RFC3339));

        // DateTimeImmutable
        $result = $normalizer->denormalize('25-06-2014 08:40:00', \DateTimeImmutable::class, null, [CarbonDateTimeNormalizer::FORMAT_KEY => 'd-m-Y H:i:s']);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result);
        $this->assertSame('2014-06-25T08:40:00+02:00', $result->format(\DateTimeImmutable::RFC3339));
    }
}
