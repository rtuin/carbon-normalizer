<?php

namespace Rtuin\Normalizer;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Roughly based on \Symfony\Component\Serializer\Normalizer\DateTimeNormalizer
 * But with Carbon/CarbonImmutable support.
 */
class CarbonDateTimeNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    public const FORMAT_KEY = 'datetime_format';
    public const TIMEZONE_KEY = 'datetime_timezone';

    private array $defaultContext = [
        self::FORMAT_KEY => \DateTime::RFC3339,
        self::TIMEZONE_KEY => null,
    ];

    private const SUPPORTED_DENORMALIZATION_TYPES = [
        \DateTimeInterface::class => true,
        \DateTimeImmutable::class => true,
        \DateTime::class => true,
        CarbonImmutable::class => true,
        Carbon::class => true,
        '\Illuminate\Support\Carbon' => true, // Alias for Carbon, added for increased support.
    ];

    public function __construct(array $defaultContext = [])
    {
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return __CLASS__ === static::class;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        if (!$object instanceof \DateTimeInterface) {
            throw new InvalidArgumentException('The object must implement the "\DateTimeInterface".');
        }

        $timezone = $this->getTimezone($context);

        if (null !== $timezone) {
            $object = clone $object;
            $object = $object->setTimezone($timezone);
        }

        return $object->format($context[self::FORMAT_KEY] ?? $this->defaultContext[self::FORMAT_KEY]);
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof \DateTimeInterface;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if ($data === null || $data === '') {
            throw new NotNormalizableValueException('The data is either an empty string or null, you should pass a string that can be parsed with the passed format or a valid DateTime string.');
        }

        $dateFormat = $context[self::FORMAT_KEY] ?? $this->defaultContext[self::FORMAT_KEY];
        $timezone = $this->getTimezone($context);

        if ($type === CarbonImmutable::class) {
            return CarbonImmutable::createFromFormat($dateFormat, $data, $timezone);
        } elseif ($type === Carbon::class || $type === '\Illuminate\Support\Carbon') {
            return Carbon::createFromFormat($dateFormat, $data, $timezone);
        } elseif ($type === \DateTime::class) {
            return \DateTime::createFromFormat($dateFormat, $data, $timezone);
        } elseif ($type === \DateTimeImmutable::class) {
            return \DateTimeImmutable::createFromFormat($dateFormat, $data, $timezone);
        }

        throw new NotNormalizableValueException('The type is not recognized or supported.');
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return static::SUPPORTED_DENORMALIZATION_TYPES[$type] ?? false;
    }

    private function getTimezone(array $context): ?\DateTimeZone
    {
        $dateTimeZone = $context[self::TIMEZONE_KEY] ?? $this->defaultContext[self::TIMEZONE_KEY];

        if (null === $dateTimeZone) {
            return null;
        }

        return $dateTimeZone instanceof \DateTimeZone ? $dateTimeZone : new \DateTimeZone($dateTimeZone);
    }
}
