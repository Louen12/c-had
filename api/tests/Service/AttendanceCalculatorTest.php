<?php

namespace App\Tests\Service;

use App\Entity\Absence;
use App\Entity\Market;
use App\Entity\MarketSchedule;
use App\Entity\Stall;
use App\Entity\StallAttendance;
use App\Repository\AbsenceRepository;
use App\Service\AttendanceCalculator;
use PHPUnit\Framework\TestCase;

class AttendanceCalculatorTest extends TestCase
{
    private AttendanceCalculator $calculator;
    private AbsenceRepository $absenceRepo;

    protected function setUp(): void
    {
        $this->absenceRepo = $this->createMock(AbsenceRepository::class);
        $this->calculator = new AttendanceCalculator($this->absenceRepo);
    }

    private function createMarket(string $name): Market
    {
        $market = new Market();
        $market->setName($name);
        $market->setSlug(strtolower($name));
        $market->setLatitude(48.39);
        $market->setLongitude(-4.48);

        return $market;
    }

    private function createSchedule(Market $market, int $dayOfWeek, string $start = '08:00', string $end = '13:00'): MarketSchedule
    {
        $schedule = new MarketSchedule();
        $schedule->setMarket($market);
        $schedule->setDayOfWeek($dayOfWeek);
        $schedule->setStartTime(new \DateTime($start));
        $schedule->setEndTime(new \DateTime($end));
        $market->addSchedule($schedule);

        return $schedule;
    }

    private function createStall(string $name): Stall
    {
        $stall = new Stall();
        $stall->setName($name);
        $stall->setSlug(strtolower($name));

        return $stall;
    }

    private function createAttendance(Stall $stall, MarketSchedule $schedule): StallAttendance
    {
        $attendance = new StallAttendance();
        $attendance->setStall($stall);
        $attendance->setMarketSchedule($schedule);

        return $attendance;
    }

    private function createAbsence(Stall $stall, \DateTimeInterface $date, ?Market $market = null): Absence
    {
        $absence = new Absence();
        $absence->setStall($stall);
        $absence->setDate($date);
        $absence->setMarket($market);

        return $absence;
    }

    public function testStallPresentWhenScheduledAndNoAbsence(): void
    {
        $market = $this->createMarket('quimper');
        $schedule = $this->createSchedule($market, 3); // Wednesday
        $stall = $this->createStall('boulangerie');
        $attendance = $this->createAttendance($stall, $schedule);

        $date = new \DateTimeImmutable('2026-10-21'); // Wednesday

        $result = $this->calculator->getPresentSchedules($stall, $date, [$attendance], []);

        $this->assertCount(1, $result);
        $this->assertSame($schedule, $result[0]);
    }

    public function testStallAbsentWhenGlobalAbsenceDeclared(): void
    {
        $market = $this->createMarket('quimper');
        $schedule = $this->createSchedule($market, 3);
        $stall = $this->createStall('boulangerie');
        $attendance = $this->createAttendance($stall, $schedule);

        $date = new \DateTimeImmutable('2026-10-21');
        $absence = $this->createAbsence($stall, $date);

        $result = $this->calculator->getPresentSchedules($stall, $date, [$attendance], [$absence]);

        $this->assertCount(0, $result);
    }

    public function testStallAbsentOnSpecificMarketOnly(): void
    {
        $marketA = $this->createMarket('quimper');
        $marketB = $this->createMarket('brest');
        $scheduleA = $this->createSchedule($marketA, 3);
        $scheduleB = $this->createSchedule($marketB, 3);
        $stall = $this->createStall('boulangerie');
        $attendanceA = $this->createAttendance($stall, $scheduleA);
        $attendanceB = $this->createAttendance($stall, $scheduleB);

        $date = new \DateTimeImmutable('2026-10-21');
        $absence = $this->createAbsence($stall, $date, $marketA);

        $result = $this->calculator->getPresentSchedules(
            $stall,
            $date,
            [$attendanceA, $attendanceB],
            [$absence]
        );

        $this->assertCount(1, $result);
        $this->assertSame($scheduleB, $result[0]);
    }

    public function testStallNotPresentOnWrongDay(): void
    {
        $market = $this->createMarket('quimper');
        $schedule = $this->createSchedule($market, 3); // Wednesday
        $stall = $this->createStall('boulangerie');
        $attendance = $this->createAttendance($stall, $schedule);

        $date = new \DateTimeImmutable('2026-10-22'); // Thursday

        $result = $this->calculator->getPresentSchedules($stall, $date, [$attendance], []);

        $this->assertCount(0, $result);
    }

    public function testSeasonalScheduleActiveInSeason(): void
    {
        $market = $this->createMarket('concarneau');
        $schedule = $this->createSchedule($market, 1); // Monday
        $schedule->setSeasonStart(new \DateTime('2026-06-01'));
        $schedule->setSeasonEnd(new \DateTime('2026-09-30'));

        $stall = $this->createStall('glacier');
        $attendance = $this->createAttendance($stall, $schedule);

        $inSeason = new \DateTimeImmutable('2026-07-06'); // Monday in July
        $result = $this->calculator->getPresentSchedules($stall, $inSeason, [$attendance], []);
        $this->assertCount(1, $result);
    }

    public function testSeasonalScheduleInactiveOutOfSeason(): void
    {
        $market = $this->createMarket('concarneau');
        $schedule = $this->createSchedule($market, 1);
        $schedule->setSeasonStart(new \DateTime('2026-06-01'));
        $schedule->setSeasonEnd(new \DateTime('2026-09-30'));

        $stall = $this->createStall('glacier');
        $attendance = $this->createAttendance($stall, $schedule);

        $outOfSeason = new \DateTimeImmutable('2026-11-02'); // Monday in November
        $result = $this->calculator->getPresentSchedules($stall, $outOfSeason, [$attendance], []);
        $this->assertCount(0, $result);
    }

    public function testGetPresentStallsBasic(): void
    {
        $market = $this->createMarket('morlaix');
        $schedule = $this->createSchedule($market, 6); // Saturday
        $stallA = $this->createStall('fromagerie');
        $stallB = $this->createStall('poissonnier');
        $attA = $this->createAttendance($stallA, $schedule);
        $attB = $this->createAttendance($stallB, $schedule);

        $date = new \DateTimeImmutable('2026-10-24'); // Saturday

        $result = $this->calculator->getPresentStalls($market, $date, [$attA, $attB], []);

        $this->assertCount(2, $result);
    }

    public function testGetPresentStallsExcludesAbsent(): void
    {
        $market = $this->createMarket('morlaix');
        $schedule = $this->createSchedule($market, 6);
        $stallA = $this->createStall('fromagerie');
        $stallB = $this->createStall('poissonnier');
        $attA = $this->createAttendance($stallA, $schedule);
        $attB = $this->createAttendance($stallB, $schedule);

        $date = new \DateTimeImmutable('2026-10-24');
        $absence = $this->createAbsence($stallB, $date);

        $result = $this->calculator->getPresentStalls($market, $date, [$attA, $attB], [$absence]);

        $this->assertCount(1, $result);
        $this->assertSame('fromagerie', $result[0]->getName());
    }

    public function testGetPresentStallsEmptyWhenNoScheduleForDay(): void
    {
        $market = $this->createMarket('landerneau');
        $this->createSchedule($market, 5); // Friday

        $date = new \DateTimeImmutable('2026-10-24'); // Saturday

        $result = $this->calculator->getPresentStalls($market, $date, [], []);

        $this->assertCount(0, $result);
    }

    public function testWrappingSeasonSchedule(): void
    {
        $market = $this->createMarket('quimper-noel');
        $schedule = $this->createSchedule($market, 6); // Saturday
        $schedule->setSeasonStart(new \DateTime('2026-11-01'));
        $schedule->setSeasonEnd(new \DateTime('2027-01-15'));

        $stall = $this->createStall('vin-chaud');
        $attendance = $this->createAttendance($stall, $schedule);

        $inDec = new \DateTimeImmutable('2026-12-19'); // Saturday
        $result = $this->calculator->getPresentSchedules($stall, $inDec, [$attendance], []);
        $this->assertCount(1, $result);

        $inJan = new \DateTimeImmutable('2027-01-09'); // Saturday in January
        $result = $this->calculator->getPresentSchedules($stall, $inJan, [$attendance], []);
        $this->assertCount(1, $result);

        $outMar = new \DateTimeImmutable('2027-03-06'); // Saturday
        $result = $this->calculator->getPresentSchedules($stall, $outMar, [$attendance], []);
        $this->assertCount(0, $result);
    }
}
