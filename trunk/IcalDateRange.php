<?php

class IcalDateRange {

	static public function this_day(){
		$start = Date::this_day();
		$end  = Date::next_day();
		return array(Ical::timestamp2ICal($start), Ical::timestamp2ICal($end));
	}

	static public function this_week(){
		$start = Date::this_week();
		$end  = Date::next_week();
		return array(Ical::timestamp2ICal($start), Ical::timestamp2ICal($end));
	}

	static public function week($week){
		$now = Date::week_number();
		$start = Date::this_week() + ($week - $now) * 7 * 24 * 60 * 60;
		$end   = Date::next_week() + ($week - $now) * 7 * 24 * 60 * 60;
		return array(Ical::timestamp2ICal($start), Ical::timestamp2ICal($end));
	}

	static public function this_month(){
		$start = Date::this_month();
		$end  = Date::next_month();
		return array(Ical::timestamp2ICal($start), Ical::timestamp2ICal($end));
	}
	static public function this_year(){
		$start = Date::this_year();
		$end  = Date::next_year();
		return array(Ical::timestamp2ICal($start), Ical::timestamp2ICal($end));
	}
}

?>
