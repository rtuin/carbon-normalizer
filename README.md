Carbon+DateTime Normalizer
=================

This is a Normalizer for the Symfony Serializer package that supports normalizing
to and from `Carbon`, `CarbonImmutable`, `Illuminate\Support\Carbon`, `DateTime` and 
`DateTimeImmutable`.

Usage example
-------------

```php
$normalizer = new \Rtuin\Normalizer\CarbonDateTimeNormalizer();

$normalized = $normalizer->normalize(\Carbon\CarbonImmutable::now());
// $normalized is now a string '2019-05-28T07:25:00+02:00'


$result = $normalizer->denormalize('2019-05-28T07:25:00+02:00', CarbonImmutable::class);
// $result is now a CarbonImmutable instance
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
