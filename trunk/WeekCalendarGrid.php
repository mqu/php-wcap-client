<?php

class WeekCalendarGrid {
	protected $name;
	protected $granularity;
	protected $data;
	protected $info = array();
	protected $user_data = null;

	public function __construct($week, $name, $granularity=2){
		$this->events = array();
		$this->name = $name;
		$this->week = $week;
		$this->data = array();
		$this->granularity = $granularity;
	}
	
	public function set_info($info){
		$this->info = $info;
	}

	public function get_info(){
		return $this->info;
	}

	public function add_event($ev){

		# si l'évenement n'est pas dans la semaine concernée, on ignore.
		if(!$this->event_in_week($ev, $this->week)){
			# echo "<pre> ev not in week " ; print_r($ev) ; echo "</pre>\n";
			return;
		}

		$this->events[] = $ev;

		$time = $ev->start();
		$pos = $this->time_to_pos($time);
		$step = $this->step();
		$len = intval($ev->length()/60) / $step;

		# for event len, fill each cells of the array.
		for($i=0 ; $i<$len ; $i++)
			$this->data[$pos + $i] = $ev;
	}

	public function event_in_week($ev, $week){

		$week_time = Date::week_to_timestamp($week);  # timestamp for week number
		$week_len = 60*60*24*7; # week length in seconds

		if($this->between($ev->start(), $week_time, $week_time+$week_len))
			return true;

		if($this->between($ev->start()+$ev->length(), $week_time, $week_time+$week_len))
			return true;

		if($this->between($week_time, $ev->start(), $ev->start() + $ev->length()))
			return true;


		return false;
	}

	protected function between($val, $start, $end){
		if($val < $start)
			return false;
		if($val > $end)
			return false;
		return true;
	}

	# number of elements for an hour.
	public function granularity(){
		return $this->granularity;
	}

	# this is in fact len of an element in minutes.
	protected function step(){
		return intval(60 / $this->granularity());
	}

	public function time_to_pos($time){

		$w = Date::week_number($time);
		$d = Date::week_day_number($time);
		$h = Date::hour($time);
		$m = intval(Date::minute($time) / $this->step());

		$pos = $w*7 + $d;
		$pos = $pos*24 + $h;
		return ($this->granularity() * $pos) + $m;
	}

	# reverse of time_to_pos
	public function pos_to_hour($pos){
		return intval($pos / $this->granularity());
	}

	public function pos_to_minute($pos){
		return intval($pos % $this->granularity());
	}

	public function name(){
		return $this->name;
	}

	public function short_name(){
		if($this->name == 'AMPHITHEATRE')
			return 'AMPHI';

		if($this->name == 'M010BIS')
			return 'M010B';

		return $this->name;
	}

	public function get_data($day, $pos){
		$p = ($this->week * 7);
		$p = ($p+$day)*24 + $pos;

		if(isset($this->data[$p]))
			return $this->data[$p];
		else
			return false;
	}

	# share a context data between differents objects
	public function set_user_data($data){
		$this->user_data = $data;
	}

	public function get_user_data(){
		return $this->user_data;
	}
}
?>
