<?php

namespace App\Service;

use App\Entity\Absence;
use App\Entity\Market;
use App\Entity\MarketSchedule;
use App\Entity\Stall;
use App\Entity\StallAttendance;
use App\Repository\AbsenceRepository;

class AttendanceCalculator
{
    public function __construct(
        private readonly AbsenceRepository $absenceRepository,
    ) {
    }

    /**
     * @param StallAttendance[] $attendances
     * @param Absence[]|null    $absences Pre-loaded absences (skips DB query)
     *
     * @return MarketSchedule[] Schedules where the stall is present
     */
    public function getPresentSchedules(Stall $stall, \DateTimeInterface $date, array $attendances, ?array $absences = null): array
    {
        $dayOfWeek = (int) $date->format('N');

        $activeAttendances = array_filter(
            $attendances,
            fn (StallAttendance $a) => $a->getStall() === $stall
                && $a->getMarketSchedule()->getDayOfWeek() === $dayOfWeek
                && $a->getMarketSchedule()->isActiveOn($date)
        );

        if (empty($activeAttendances)) {
            return [];
        }

        $absences ??= $this->absenceRepository->findByStallAndDate($stall, $date);

        $absentMarketIds = [];
        $absentAll = false;
        foreach ($absences as $absence) {
            if ($absence->getMarket() === null) {
                $absentAll = true;
                break;
            }
            $absentMarketIds[] = $absence->getMarket()->getId()->toRfc4122();
        }

        if ($absentAll) {
            return [];
        }

        $present = [];
        foreach ($activeAttendances as $attendance) {
            $market = $attendance->getMarketSchedule()->getMarket();
            $marketId = $market->getId()->toRfc4122();

            if (!in_array($marketId, $absentMarketIds, true)) {
                $present[] = $attendance->getMarketSchedule();
            }
        }

        return $present;
    }

    /**
     * @param StallAttendance[] $allAttendances All attendances for the market's schedules
     * @param Absence[]         $allAbsences    All absences for the date
     *
     * @return Stall[] Stalls present at the market on the given date
     */
    public function getPresentStalls(Market $market, \DateTimeInterface $date, array $allAttendances, array $allAbsences = []): array
    {
        $dayOfWeek = (int) $date->format('N');

        $scheduleIds = [];
        foreach ($market->getSchedules() as $schedule) {
            if ($schedule->getDayOfWeek() === $dayOfWeek && $schedule->isActiveOn($date)) {
                $scheduleIds[] = $schedule->getId();
            }
        }

        if (empty($scheduleIds)) {
            return [];
        }

        $stallAttendances = [];
        foreach ($allAttendances as $attendance) {
            if (in_array($attendance->getMarketSchedule()->getId(), $scheduleIds, true)) {
                $stallId = $attendance->getStall()->getId()->toRfc4122();
                $stallAttendances[$stallId] = $attendance->getStall();
            }
        }

        $absentStallIds = [];
        foreach ($allAbsences as $absence) {
            $stallId = $absence->getStall()->getId()->toRfc4122();
            if ($absence->getMarket() === null || $absence->getMarket() === $market) {
                $absentStallIds[] = $stallId;
            }
        }

        return array_values(array_filter(
            $stallAttendances,
            fn (Stall $stall) => !in_array($stall->getId()->toRfc4122(), $absentStallIds, true)
        ));
    }
}
