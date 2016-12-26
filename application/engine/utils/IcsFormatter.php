<?php
class IcsFormatter {
	public $version = '1.0.0';

	const CLASS_PUBLIC = "PUBLIC";
	const CLASS_PRIVATE = "PRIVATE";
	const CLASS_CONFIDENTIAL = "CONFIDENTIAL";

	public $company = "ArmagNet";
	public $product = "IcsFormatter";

	public function format($entries) {
		if ($entries && !is_array($entries)) {
			$entries = array($entries);
		}

		$content = "";

		$content .= "BEGIN:VCALENDAR" . "\r\n";
		$content .= "VERSION:2.0" . "\r\n";
		$content .= "PRODID:-//" . $this->company . "//NONSGML " . $this->product . "//EN" . "\r\n";

		foreach($entries as $entry) {
			$content .= $entry->format();
		}

		$content .= "END:VCALENDAR" . "\r\n";

		return $content;
	}

	static function formatDate($date) {
		if ($date instanceof DateTime) {
			return $date->format("Ymd\THis"). "Z";
		}

		return $date;
	}
}

interface IcsObject {
	public function format();
}

class Alarm implements IcsObject {

	const EVERY_HOUR = "PT1H";
	const EVERY_DAY = "P1D";

	const ACTION_DISPLAY = "DISPLAY";
	const ACTION_AUDIO = "AUDIO";

	const TRIGGER_EVE = "-P1D";
	const TRIGGER_HOUR = "-P0DT1H0M0S";
	const TRIGGER_15MN = "-P0DT0H15M0S";

	public $repeat = 0;
	public $duration = Alarm::EVERY_HOUR;
	public $trigger;
	public $triggerDate;
	public $action = Alarm::ACTION_DISPLAY;

	public function format() {

		$content = "";

		$content .= "BEGIN:VALARM" . "\r\n";
		$content .= "ACTION:" . $this->action . "\r\n";

		if ($this->trigger) {
			$content .= "TRIGGER:" . $this->trigger . "\r\n";
		}
		else if ($this->triggerDate) {
			$content .= "TRIGGER;VALUE=DATE-TIME:" . IcsFormatter::formatDate($this->triggerDate) . "\r\n";
		}

//		$content .= "ATTACH;FMTTYPE=audio/basic:http://host.com/pub/audio-files/ssbanner.aud" . "\r\n";

		if ($this->repeat) {
			$content .= "REPEAT:" . $this->repeat . "\r\n";
			$content .= "DURATION:" . $this->duration . "\r\n";
		}

		$content .= "END:VALARM" . "\r\n";

		return $content;
	}
}

class Todo implements IcsObject {
	const STATUS_NEEDS_ACTION		= "NEEDS-ACTION";	// Indicates to-do needs action.
	const STATUS_COMPLETED			= "COMPLETED";		// Indicates to-do completed.
	const STATUS_IN_PROCESS			= "IN-PROCESS";		// Indicates to-do in process of
	const STATUS_CANCELLED			= "CANCELLED";		// Indicates journal is removed.

	public $dueDate;
	public $categories = array();
	public $status = Todo::STATUS_IN_PROCESS;
	public $summary;
	public $alarms = array();
	public $timestamp;
	public $uid;

	public function format() {

		$content = "";

		$content .= "BEGIN:VTODO" . "\r\n";
		$content .= "DTSTAMP:" . IcsFormatter::formatDate($this->timestamp) . "\r\n";
		$content .= "SEQUENCE:0" . "\r\n";
		$content .= "UID:" . $this->uid . "\r\n";

// 		$content .= "ORGANIZER:MAILTO:unclesam@us.gov" . "\r\n";
// 		$content .= "ATTENDEE;PARTSTAT=ACCEPTED:MAILTO:jqpublic@host.com" . "\r\n";

		$content .= "DUE:" . IcsFormatter::formatDate($this->dueDate) . "\r\n";

		$content .= "STATUS:" . $this->status . "\r\n";

		if (count($this->categories)) {
			$content .= "CATEGORIES:" . implode(", ", $this->categories) . "\r\n";
		}
		$content .= "SUMMARY:" . $this->summary . "\r\n";

		// Alarms
		foreach($this->alarms as $alarm) {
			$content .= $alarm->format();
		}

		$content .= "END:VTODO" . "\r\n";

		return $content;
	}
}

class Journal implements IcsObject {
	const STATUS_DRAFT		= "DRAFT";		// Indicates journal is draft.
	const STATUS_FINAL		= "FINAL";		// Indicates journal is final.
	const STATUS_CANCELLED	= "CANCELLED";	// Indicates journal is removed.

	public $description;
	public $summary;
	public $class = IcsFormatter::CLASS_PUBLIC;
	public $categories = array();
	public $status = Journal::STATUS_FINAL;
	public $timestamp;
	public $uid;

	public function format() {

		$content = "";

		$content .= "BEGIN:VJOURNAL" . "\r\n";

		$content .= "DTSTAMP:" . IcsFormatter::formatDate($this->timestamp) . "\r\n";
		$content .= "UID:" . $this->uid . "\r\n";
		$content .= "SEQUENCE:0" . "\r\n";

//		$content .= "ORGANIZER:MAILTO:jsmith@host.com" . "\r\n";

		$content .= "STATUS:" . $this->status . "\r\n";
		$content .= "CLASS:" . $this->class . "\r\n";

		if (count($this->categories)) {
			$content .= "CATEGORIES:" . implode(", ", $this->categories) . "\r\n";
		}

		$content .= "SUMMARY:" . $this->summary . "\r\n";
		$content .= "DESCRIPTION:" . $this->description . "\r\n";

		$content .= "END:VJOURNAL" . "\r\n";

		return $content;
	}
}

class Event implements IcsObject {
	const STATUS_TENTATIVE			= "TENTATIVE";		// Indicates event is tentative.
	const STATUS_CONFIRMED			= "CONFIRMED";		// Indicates event is definite.
	const STATUS_CANCELLED			= "CANCELLED";		// Indicates event is cancelled.

	public $startDate;
	public $endDate;

	public $categories = array();
	public $status = Event::STATUS_TENTATIVE;
	public $summary;
	public $alarms = array();
	public $timestamp;
	public $uid;
	public $location;

	public function format() {

		$content = "";

		$content .= "BEGIN:VEVENT" . "\r\n";
		$content .= "DTSTAMP:" . IcsFormatter::formatDate($this->timestamp) . "\r\n";
		$content .= "SEQUENCE:0" . "\r\n";
		$content .= "UID:" . $this->uid . "\r\n";

		// 		$content .= "ORGANIZER:MAILTO:unclesam@us.gov" . "\r\n";
		// 		$content .= "ATTENDEE;PARTSTAT=ACCEPTED:MAILTO:jqpublic@host.com" . "\r\n";

		$content .= "DTSTART:" . IcsFormatter::formatDate($this->startDate) . "\r\n";
		$content .= "DTEND:" . IcsFormatter::formatDate($this->endDate) . "\r\n";

		$content .= "STATUS:" . $this->status . "\r\n";

		if (count($this->categories)) {
			$content .= "CATEGORIES:" . implode(", ", $this->categories) . "\r\n";
		}

		if ($this->location) {
			$content .= "LOCATION:" . $this->location . "\r\n";
		}

		$content .= "SUMMARY:" . $this->summary . "\r\n";

		// Alarms
		foreach($this->alarms as $alarm) {
			$content .= $alarm->format();
		}

		$content .= "END:VEVENT" . "\r\n";

		return $content;
	}
}



?>