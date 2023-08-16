<?php

namespace Rtuin\Normalizer;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Roughly based on \Symfony\Component\Serializer\Normalizer\DateTimeNormalizer
 * But with Carbon/CarbonImmutable support.
 */
class CarbonDateTimeNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public const FORMAT_KEY = 'datetime_format';
    public const TIMEZONE_KEY = 'datetime_timezone';

    private array $defaultContext = [
        self::FORMAT_KEY => DateTimeInterface::RFC3339,
        self::TIMEZONE_KEY => null,
    ];

    private const SUPPORTED_TYPES = [
        \DateTimeInterface::class => true,
        \DateTimeImmutable::class => true,
        \DateTime::class => true,
        CarbonImmutable::class => true,
        Carbon::class => true,
        '\Illuminate\Support\Carbon' => true, // Alias for Carbon, added for increased support.
    ];

    public function __construct(array $defaultContext = [])
    {
        $this->setDefaultContext($defaultContext);
    }

    public function setDefaultContext(array $defaultContext): void
    {
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function normalize(mixed $object, string $format = null, array $context = []): string
    {
        if (!$object instanceof \DateTimeInterface) {
            throw new InvalidArgumentException('The object must implement the "\DateTimeInterface".');
        }

        $dateTimeFormat = $context[self::FORMAT_KEY] ?? $this->defaultContext[self::FORMAT_KEY];
        $timezone = $this->getTimezone($context);

        if (null !== $timezone) {
            $object = clone $object;
            $object = $object->setTimezone($timezone);
        }

        return $object->format($dateTimeFormat);
    }

    /**
     * @param array $context
     */
    public function supportsNormalization(mixed $data, string $format = null /* , array $context = [] */): bool
    {
        return $data instanceof \DateTimeInterface;
    }

    /**
     * @throws NotNormalizableValueException
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): \DateTimeInterface
    {
        $dateTimeFormat = $context[self::FORMAT_KEY] ?? $this->defaultContext[self::FORMAT_KEY];
        $timezone = $this->getTimezone($context);

        if (null === $data || (\is_string($data) && '' === trim($data))) {
            throw NotNormalizableValueException::createForUnexpectedDataType('The data is either an empty string or null, you should pass a string that can be parsed with the passed format or a valid DateTime string.', $data, [Type::BUILTIN_TYPE_STRING], $context['deserialization_path'] ?? null, true);
        }

        try {
            if ($type === CarbonImmutable::class) {
                return CarbonImmutable::createFromFormat($dateTimeFormat, $data, $timezone);
            }

            if ($type === Carbon::class || $type === '\Illuminate\Support\Carbon') {
                return Carbon::createFromFormat($dateTimeFormat, $data, $timezone);
            }

            if ($type === \DateTime::class) {
                return \DateTime::createFromFormat($dateTimeFormat, $data, $timezone);
            }

            if ($type === \DateTimeImmutable::class) {
                return \DateTimeImmutable::createFromFormat($dateTimeFormat, $data, $timezone);
            }
        } catch (\Exception $e) {
            throw NotNormalizableValueException::createForUnexpectedDataType($e->getMessage(), $data, [Type::BUILTIN_TYPE_STRING], $context['deserialization_path'] ?? null, false, $e->getCode(), $e);
        }

        throw new NotNormalizableValueException('The type is not recognized or supported.');
    }

    /**
     * @param array $context
     */
    public function supportsDenormalization(mixed $data, string $type, string $format = null /* , array $context = [] */): bool
    {
        return isset(self::SUPPORTED_TYPES[$type]);
    }

    private function getTimezone(array $context): ?\DateTimeZone
    {
        $dateTimeZone = $context[self::TIMEZONE_KEY] ?? $this->defaultContext[self::TIMEZONE_KEY];

        if (null === $dateTimeZone) {
            return null;
        }

        return $dateTimeZone instanceof \DateTimeZone ? $dateTimeZone : new \DateTimeZone($dateTimeZone);
    }

    public function getSupportedTypes(?string $format): array
    {
        return self::SUPPORTED_TYPES;
    }
}
