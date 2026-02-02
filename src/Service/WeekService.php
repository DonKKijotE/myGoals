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
    public function getWeek(\DateTimeInterface $date = null): array
    {
        $today = $date ? clone $date : new \DateTimeImmutable();

        $start = (clone $today)
            ->modify('monday this week')
            ->setTime(0, 0, 0);

        $end = (clone $start)
            ->modify('sunday this week')
            ->setTime(23, 59, 59);

        return [
            'start' => $start,
            'end' => $end,
        ];
    }
}
