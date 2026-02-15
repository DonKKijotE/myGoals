<?php
// src/Service/DateTimeService.php
namespace App\Service;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

class DateTimeService
{
    private string $defaultTimezone;

    public function __construct(string $defaultTimezone = 'Atlantic/Canary')
    {
        $this->defaultTimezone = $defaultTimezone;
    }

    public function toUserTime(DateTimeInterface $date, ?string $userTimezone = null): DateTimeImmutable
    {
        $tz = $userTimezone ?? $this->defaultTimezone;

        // Si ya es DateTimeImmutable, solo cambia la TZ
        if ($date instanceof \DateTimeImmutable) {
            return $date->setTimezone(new \DateTimeZone($tz));
        }

        // Si es mutable, crea immutable y cambia TZ
        return \DateTimeImmutable::createFromMutable($date)->setTimezone(new \DateTimeZone($tz));
    }

    public function toUtc(DateTimeInterface $date, ?string $userTimezone = null): DateTimeImmutable
    {
        $tz = $userTimezone ?? $this->defaultTimezone;

        if ($date instanceof \DateTimeImmutable) {
            return $date->setTimezone(new \DateTimeZone('UTC'));
        }

        return \DateTimeImmutable::createFromMutable($date)->setTimezone(new \DateTimeZone('UTC'));
    }
}
