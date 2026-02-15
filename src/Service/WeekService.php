<?php

// src/Service/SemanaService.php

namespace App\Service;

class WeekService
{
    /**
     * Returns the start and end of the week for a given date.
     *
     * @param \DateTimeInterface|null $date If not provided, uses current date
     * @return array ['start' => \DateTime, 'end' => \DateTime]
     */
     public function getWeek(
          \DateTimeInterface $date = null,
          string $timezone = 'UTC'
      ): array {
          $tz = new \DateTimeZone($timezone);

          $today = $date
              ? new \DateTimeImmutable($date->format('Y-m-d H:i:s'), $tz)
              : new \DateTimeImmutable('now', $tz);

          $start = $today
              ->modify('monday this week')
              ->setTime(0, 0, 0);

          $end = $start
              ->modify('sunday this week')
              ->setTime(23, 59, 59);

          return [
              'start' => $start,
              'end' => $end,
          ];
      }
}
