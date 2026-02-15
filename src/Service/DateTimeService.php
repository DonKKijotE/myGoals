<?php
// src/Service/DateTimeService.php
namespace App\Service;

use DateTime;
use DateTimeInterface;
use DateTimeZone;

class DateTimeService
{
    private string $defaultTimezone;

    public function __construct(string $defaultTimezone = 'Atlantic/Canary')
    {
        $this->defaultTimezone = $defaultTimezone;
    }

    // Convierte cualquier fecha del usuario a UTC antes de guardar
    public function toUtc(DateTimeInterface $date, ?string $userTimezone = null): DateTime
    {
        $tz = $userTimezone ?? $this->defaultTimezone;
        $userTime = new DateTime($date->format('Y-m-d H:i:s'), new DateTimeZone($tz));
        $userTime->setTimezone(new DateTimeZone('UTC'));

        return $userTime;
    }

    // Convierte UTC a la zona del usuario para mostrar
    public function toUserTime(DateTimeInterface $date, ?string $userTimezone = null): DateTime
    {
        $tz = $userTimezone ?? $this->defaultTimezone;
        $userTime = clone $date;
        $userTime->setTimezone(new \DateTimeZone($tz));

        return $userTime;
    }
}
