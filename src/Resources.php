<?php

declare(strict_types=1);

namespace KhaleejiAPI\Resources;

use KhaleejiAPI\KhaleejiAPI;

/**
 * Validation APIs: email, phone, IBAN, VAT/TRN, Emirates ID, Saudi ID
 */
class ValidationResource
{
    public function __construct(private readonly KhaleejiAPI $client) {}

    /** Validate an email address */
    public function validateEmail(string $email): array
    {
        return $this->client->get('/email/validate', ['email' => $email]);
    }

    /** Validate a phone number */
    public function validatePhone(string $phone, ?string $country = null): array
    {
        return $this->client->get('/phone/validate', [
            'phone' => $phone,
            'country' => $country,
        ]);
    }

    /** Validate an IBAN */
    public function validateIBAN(string $iban): array
    {
        return $this->client->get('/iban/validate', ['iban' => $iban]);
    }

    /** Validate a VAT/TRN number */
    public function validateVAT(string $trn, ?string $countryCode = null): array
    {
        return $this->client->get('/vat/validate', [
            'trn' => $trn,
            'country' => $countryCode,
        ]);
    }

    /** Validate a UAE Emirates ID */
    public function validateEmiratesID(string $id): array
    {
        return $this->client->get('/emirates-id/validate', ['id' => $id]);
    }

    /** Validate a Saudi National ID or Iqama */
    public function validateSaudiID(string $id): array
    {
        return $this->client->get('/saudi-id/validate', ['id' => $id]);
    }

    /**
     * Batch validate Saudi IDs (max 100)
     * @param string[] $ids
     */
    public function validateSaudiIDBatch(array $ids): array
    {
        return $this->client->post('/saudi-id/validate', ['ids' => $ids]);
    }
}

/**
 * Geolocation APIs: IP lookup, timezone, geocoding
 */
class GeoResource
{
    public function __construct(private readonly KhaleejiAPI $client) {}

    /** Look up IP geolocation data */
    public function ipLookup(?string $ip = null): array
    {
        return $this->client->get('/ip/lookup', ['ip' => $ip]);
    }

    /** Get timezone data for a location */
    public function getTimezone(string $location): array
    {
        return $this->client->get('/timezone', ['location' => $location]);
    }

    /** Geocode an address or coordinates */
    public function geocode(string $query, ?string $country = null, ?string $lang = null): array
    {
        return $this->client->get('/geocode', [
            'q' => $query,
            'country' => $country,
            'lang' => $lang,
        ]);
    }
}

/**
 * Finance APIs: exchange rates, VAT calculation, holidays, business days
 */
class FinanceResource
{
    public function __construct(private readonly KhaleejiAPI $client) {}

    /**
     * Get exchange rates
     * @param string[] $symbols
     */
    public function getExchangeRates(string $base = 'AED', ?array $symbols = null): array
    {
        return $this->client->get('/exchange/rates', [
            'base' => $base,
            'symbols' => $symbols ? implode(',', $symbols) : null,
        ]);
    }

    /** Calculate VAT */
    public function calculateVAT(float $amount, string $country = 'AE', bool $inclusive = false): array
    {
        return $this->client->get('/vat/calculate', [
            'amount' => (string) $amount,
            'country' => $country,
            'inclusive' => $inclusive ? 'true' : 'false',
        ]);
    }

    /** Get public holidays for a GCC country */
    public function getHolidays(
        string $country = 'AE',
        ?int $year = null,
        ?string $mode = null,
        ?string $date = null,
        ?int $month = null,
    ): array {
        return $this->client->get('/holidays', [
            'country' => $country,
            'year' => $year !== null ? (string) $year : null,
            'mode' => $mode,
            'date' => $date,
            'month' => $month !== null ? (string) $month : null,
        ]);
    }

    /** Calculate business days */
    public function getBusinessDays(
        string $country = 'AE',
        ?string $date = null,
        ?string $from = null,
        ?string $to = null,
        ?int $add = null,
    ): array {
        return $this->client->get('/business-days', [
            'country' => $country,
            'date' => $date,
            'from' => $from,
            'to' => $to,
            'add' => $add !== null ? (string) $add : null,
        ]);
    }
}

/**
 * Communication APIs: AI-powered translation
 */
class CommunicationResource
{
    public function __construct(private readonly KhaleejiAPI $client) {}

    /** Translate text using AI (Google Gemini) */
    public function translate(
        string $text,
        string $target,
        ?string $source = null,
        ?string $formality = null,
        ?string $dialect = null,
    ): array {
        $body = ['text' => $text, 'target' => $target];
        if ($source !== null) $body['source'] = $source;
        if ($formality !== null) $body['formality'] = $formality;
        if ($dialect !== null) $body['dialect'] = $dialect;

        return $this->client->post('/translate', $body);
    }
}

/**
 * Islamic APIs: Hijri calendar, prayer times, Arabic text processing
 */
class IslamicResource
{
    public function __construct(private readonly KhaleejiAPI $client) {}

    /** Convert between Gregorian and Hijri calendars */
    public function convertHijri(
        ?string $date = null,
        ?string $hijri = null,
        bool $today = false,
    ): array {
        return $this->client->get('/hijri/convert', [
            'date' => $date,
            'hijri' => $hijri,
            'today' => $today ? 'true' : null,
        ]);
    }

    /** Get prayer times for a location */
    public function getPrayerTimes(
        ?string $city = null,
        ?float $lat = null,
        ?float $lng = null,
        ?string $date = null,
        string $method = 'mwl',
        string $school = 'shafi',
    ): array {
        return $this->client->get('/prayer-times', [
            'city' => $city,
            'lat' => $lat !== null ? (string) $lat : null,
            'lng' => $lng !== null ? (string) $lng : null,
            'date' => $date,
            'method' => $method,
            'school' => $school,
        ]);
    }

    /** Process Arabic text */
    public function processArabic(
        string $text,
        string $operation,
        ?string $direction = null,
    ): array {
        $body = ['text' => $text, 'operation' => $operation];
        if ($direction !== null) {
            $body['options'] = ['direction' => $direction];
        }

        return $this->client->post('/arabic/process', $body);
    }
}

/**
 * Utility APIs: weather, QR code, URL shortener, fraud check
 */
class UtilityResource
{
    public function __construct(private readonly KhaleejiAPI $client) {}

    /** Get weather for a city */
    public function getWeather(string $city): array
    {
        return $this->client->get('/weather', ['city' => $city]);
    }

    /** Check for fraud */
    public function fraudCheck(
        ?string $ip = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $name = null,
    ): array {
        $body = [];
        if ($ip !== null) $body['ip'] = $ip;
        if ($email !== null) $body['email'] = $email;
        if ($phone !== null) $body['phone'] = $phone;
        if ($name !== null) $body['name'] = $name;

        return $this->client->post('/fraud/check', $body);
    }

    /** Shorten a URL */
    public function shortenURL(string $url, ?string $customCode = null): array
    {
        $body = ['url' => $url];
        if ($customCode !== null) $body['customCode'] = $customCode;

        return $this->client->post('/url/shorten', $body);
    }
}
