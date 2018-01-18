<?php

namespace ProcessWire;

class BusinessHours extends WireData
{

    public function __construct()
    {
        parent::__construct();

        try {
            $this->set(1, null);
            $this->set(2, null);
            $this->set(3, null);
            $this->set(4, null);
            $this->set(5, null);
            $this->set(6, null);
            $this->set(7, null);
        } catch (WireException $e) {
        }
    }

    /**
     * @param int $key
     * @param BusinessHoursDay|null $value
     * @return $this
     * @throws \ProcessWire\WireException
     */
    public function set($key, $value)
    {
        $key = (int)$key;
        if ($key < 1 || $key > 7) {
            throw new WireException('Only ISO-8601 numeric representations of the day of the week are allowed as key.');
        }

        if ($value !== null && !$value instanceof BusinessHoursDay) {
            throw new WireException('Value must be an instance of BusinessHoursDay');
        }

        return parent::set($key, $value);
    }

    /**
     * @param int $key
     * @return \ProcessWire\BusinessHoursDay
     * @throws \ProcessWire\WireException
     */
    public function get($key)
    {
        $key = (int)$key;
        if ($key < 1 || $key > 7) {
            throw new WireException('Only ISO-8601 numeric representations of the day of the week are allowed as key.');
        }

        return parent::get($key);
    }

    /**
     * @return null|\ProcessWire\BusinessHoursDay
     */
    public function getToday()
    {
        $day = date('N');

        try {
            $today = $this->get($day);
        } catch (WireException $e) {
            return null;
        }

        return $today;
    }

    /**
     * @return bool
     */
    public function isNowOpen()
    {
        $day = date('N');

        try {
            $today = $this->get($day);

            if ($today != null) {
                $isOpen = $today->inRange(date('H:i'));
                if ($isOpen) {
                    return true;
                }
                // if not open, check if the prev day has a to-time that is smaller than the from-time
                $yesterday = $this->get($day == 1 ? 7 : $day - 1);
                $timeToCheck = new \DateTimeImmutable(date('H:i'));
                foreach($yesterday->getEntries() as $entry) {
                    if($entry->getTo() < $entry->getFrom() && $entry->getTo() > $timeToCheck) {
                        return true;
                    }
                }
            }

        } catch (WireException $e) {
            return false;
        }

        return false;
    }
}

class BusinessHoursDay
{

    private $day;
    private $entries;

    /**
     * BusinessHoursDay constructor.
     * @param int $day
     * @param BusinessHoursEntry[] $entries
     * @throws \ProcessWire\WireException
     */
    public function __construct($day, $entries)
    {
        $day = (int)$day;
        if ($day < 1 || $day > 7) {
            throw new WireException('Only ISO-8601 numeric representations of the day of the week are allowed.');
        }
        $this->day = $day;

        if (!is_array($entries)) {
            throw new WireException('Entries must be a BusinessHoursEntry[] array');
        }
        foreach ($entries as $entry) {
            if (!$entry instanceof BusinessHoursEntry) {
                throw new WireException('Entries must be a BusinessHoursEntry[] array');
            }
        }
        $this->entries = $entries;
    }

    /**
     * @return int
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @return \ProcessWire\BusinessHoursEntry[]
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @param string $time
     * @return bool
     */
    public function inRange($time)
    {
        $timeToCheck = new \DateTimeImmutable($time);

        foreach ($this->entries as $entry) {
            $to = $entry->getTo();
            if ($to < $entry->getFrom()) {
                // the to-time is on the next day so we set the to-time to the last time of the day
                $to = new \DateTimeImmutable('23:59:59');
            }
            if ($timeToCheck >= $entry->getFrom() && $timeToCheck < $to) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!count($this->entries)) {
            return '';
        }

        $parts = array();
        foreach ($this->entries as $entry) {
            $parts[] = $entry->getFrom()->format('H:i') . '-' . $entry->getTo()->format('H:i');
        }

        return join(', ', $parts);
    }

}

class BusinessHoursEntry
{
    /** @var \DateTimeImmutable */
    private $from;

    /** @var \DateTimeImmutable */
    private $to;

    /**
     * BusinessHoursEntry constructor.
     * @param string $fromTime
     * @param string $toTime
     */
    public function __construct($fromTime, $toTime)
    {
        $this->from = new \DateTimeImmutable($fromTime);
        $this->to = new \DateTimeImmutable($toTime);
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getTo()
    {
        return $this->to;
    }

}