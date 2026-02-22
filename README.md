# KhaleejiAPI PHP SDK

Official PHP SDK for [KhaleejiAPI](https://khaleejiapi.dev) — the MENA region's developer API platform.

## Requirements

- PHP 8.1+
- Guzzle 7.0+

## Installation

```bash
composer require khaleejiapi/sdk
```

## Quick Start

```php
<?php

use KhaleejiAPI\KhaleejiAPI;

$api = new KhaleejiAPI('kapi_live_your_key_here');

// Validate an email
$email = $api->validation->validateEmail('user@example.com');
echo $email['valid']; // true

// Get prayer times
$prayers = $api->islamic->getPrayerTimes(city: 'Dubai');
echo $prayers['prayers']['fajr']; // "05:12"

// Exchange rates
$rates = $api->finance->getExchangeRates('AED', ['USD', 'EUR', 'SAR']);
print_r($rates['rates']);
```

## API Reference

### Validation

```php
// Email validation
$result = $api->validation->validateEmail('user@example.com');

// Phone validation
$phone = $api->validation->validatePhone('+971501234567', 'AE');

// IBAN validation
$iban = $api->validation->validateIBAN('AE070331234567890123456');

// VAT/TRN validation
$vat = $api->validation->validateVAT('100123456700003');

// Emirates ID validation
$eid = $api->validation->validateEmiratesID('784-1990-1234567-1');

// Saudi ID validation
$sid = $api->validation->validateSaudiID('1012345678');

// Saudi ID batch validation (max 100)
$batch = $api->validation->validateSaudiIDBatch(['1012345678', '2098765432']);
```

### Geolocation

```php
// IP geolocation
$ip = $api->geo->ipLookup('8.8.8.8');

// Timezone lookup
$tz = $api->geo->getTimezone('Dubai');

// Geocoding
$geo = $api->geo->geocode('Burj Khalifa, Dubai');
```

### Finance

```php
// Exchange rates
$rates = $api->finance->getExchangeRates('AED', ['USD', 'EUR']);

// VAT calculation
$vat = $api->finance->calculateVAT(100.0, 'AE');

// Public holidays
$holidays = $api->finance->getHolidays('AE', 2026);

// Business days
$days = $api->finance->getBusinessDays('AE', from: '2026-01-01', to: '2026-01-31');
```

### Communication

```php
// AI Translation (powered by Google Gemini)
$translation = $api->communication->translate(
    text: 'Hello, world!',
    target: 'ar',
    dialect: 'gulf',
);
```

### Islamic

```php
// Hijri calendar conversion
$hijri = $api->islamic->convertHijri(today: true);

// Prayer times
$prayers = $api->islamic->getPrayerTimes(city: 'Mecca', method: 'umm_al_qura');

// Arabic text processing
$arabic = $api->islamic->processArabic(
    text: 'بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ',
    operation: 'removeDiacritics',
);
```

### Utility

```php
// Weather
$weather = $api->utility->getWeather('Dubai');

// Fraud check
$fraud = $api->utility->fraudCheck(email: 'test@example.com', ip: '1.2.3.4');

// URL shortener
$short = $api->utility->shortenURL('https://example.com/very-long-url');
```

## Configuration

```php
// Simple initialization
$api = new KhaleejiAPI('kapi_live_your_key');

// Full configuration
$api = new KhaleejiAPI(
    apiKey: 'kapi_live_your_key',
    baseUrl: 'https://khaleejiapi.dev/api/v1',
    timeout: 30.0,
    maxRetries: 2,
);
```

## Error Handling

```php
use KhaleejiAPI\KhaleejiAPIException;

try {
    $result = $api->validation->validateEmail('test@example.com');
} catch (KhaleejiAPIException $e) {
    echo "Error {$e->getStatusCode()}: {$e->getMessage()}\n";
    echo "Code: {$e->getErrorCode()}\n";

    if ($e->getStatusCode() === 429) {
        $rateLimitInfo = $e->getRateLimitInfo();
        echo "Retry after: {$rateLimitInfo['reset']} seconds\n";
    }
}
```

## Laravel Integration

```php
// config/services.php
'khaleejiapi' => [
    'key' => env('KHALEEJI_API_KEY'),
],

// AppServiceProvider.php
$this->app->singleton(KhaleejiAPI::class, function () {
    return new KhaleejiAPI(config('services.khaleejiapi.key'));
});

// In a controller
public function validateEmail(Request $request, KhaleejiAPI $api)
{
    $result = $api->validation->validateEmail($request->email);
    return response()->json($result);
}
```

## License

MIT — See [LICENSE](LICENSE) for details.

## Links

- [Documentation](https://khaleejiapi.dev/docs)
- [API Reference](https://khaleejiapi.dev/docs/v1)
- [Dashboard](https://khaleejiapi.dev/dashboard)
- [Status](https://khaleejiapi.dev/status)
