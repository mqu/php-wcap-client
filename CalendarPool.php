<?php

require_once('RoomInfo.php');

class CalendarPool{
	protected $calendars;
	protected $config;

	public function __construct($config = array()){
		$this->calendars = array();

		# default config values
		$this->config = array(
			'start'       => 8,
			'end'         => 18,
			'granularity' => 4 # can be 1, 2, 4, 6 : number of fractions for an hour.
		);

		foreach($config as $key=>$val){
			$this->config[$key] = $val;
		}
	}

	public function add($cal){
		$this->calendars[] = $cal;
	}

	public function start(){
		return $this->config['start'];
	}

	public function end(){
		return $this->config['end'];
	}

	public function steps(){
		return (($this->end() - $this->start()) * $this->granularity());
	}

	# number of units for an hour : can be 1, 2, 4
	public function granularity(){
		return $this->config['granularity'];
	}

	public function render($context = array()){

		$days = array('dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi');
		$week = $context['week'];
		$date_format = "d/m/Y";
		$w = count($this->calendars);

		$cal_render = new HtmlCalendarDay($w, $this->steps(), $class='calendar', $this->config);
		$cal_render->set_data_source($this);  # html_calendar will call $this->get_data() when rendering
		
		foreach($days as $idx => $day){
			if($day == 'dimanche')
				continue;

			$date = date($date_format, Date::week_to_timestamp($week) + ($idx-1)*60*60*24);

			echo "<b>$day</b> ($date)\n";
			$cal_render->render($idx);
			echo "<br>\n";
		}
	}

	# public function get_data($row, $col){
	public function get_data($pos, $cal_idx, $user_data){
		global $config;

		$format = 'H:i'; # date format
		$content = array(
			'title'   => 'libre',
			'value'   => "&nbsp;",
			'class'   => 'free'
		);

		# if pos == -1 -> table header
		if($pos == -1){
			$cal = $this->calendars[$cal_idx];
			$info = $cal->get_info(); # information sur les salles de réunion

			if(get_class($info) == 'RoomInfo'){

			# URL : https://mail.../mail/svc/calendar/anon/view?calid=dsna-dti-mnd-sallea209-rs@aviation-civile.gouv.fr&tzid=Europe/Paris
				$res_name = sprintf($config['calendar/room/format-string'],strtolower($cal->name()));
				$url = sprintf('%smail/svc/calendar/anon/view?calid=%s&tzid=%s',
					$config['site/webmail-url'],
					$res_name,
					$config['app/sys/timezone']);

				$list = array(
					sprintf('<a href="%s">%s</a>', $url, $cal->short_name()),
					$info->capacity(),
					$info->type_short()
				);
	

				$content['value'] = join("<br>\n", $list);
				$content['title'] = sprintf("salle %s<br>", $cal->name()) . $info->toHtml();
				$content['class'] = 'tooltip green';
			} else {
				if($info->initials() != '')
					$content['value'] = sprintf('<i>%s</i>', $info->initials());
				else
					$content['value'] = '*';

				$content['title'] = sprintf("agenda %s<br>", $cal->name());
				$content['class'] = 'tooltip default';

			}
			return $content;
		}

		$day = $user_data;

		if(isset($this->calendars[$cal_idx])){
			$cal = $this->calendars[$cal_idx];

			$hour = $this->start() + $cal->pos_to_hour($pos);
			$gra = $cal->granularity($pos);

			if($hour == 12 || $hour == 13)
				$content['class'] = 'light_green';

			$pos = $this->start() * $this->granularity() + $pos;
			$ev = $cal->get_data($day, $pos);
			if($ev !== false){
				$content['class'] = 'busy';
				$content['title'] = sprintf('%s : %s (%s) : %s',
					$cal->name(),
					$ev->start($format),
					$ev->length_as_text(),
					$ev->summary());
			} else{
				$ical = $cal->get_user_data();
				if($ical->errno() != WCAP_ERR_OK){
					$content['title'] = sprintf("%s : inconnu (%s)", $cal->name(), WcapClient::error_to_string($ical->errno()));
					$content['class'] = 'unknown';
				}
				else
					$content['title'] = sprintf("%s : libre", $cal->name());

			}
		}

		return $content;
	}

	# not used in real world : for debuging
	public function render_as_text(){
		# foreach($this->calendars as $cal)
		# 	$cal->render();
		$days = array('sunday', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi');
		$start = $this->start(); # start hour;
		$end = $this->end();  # end hour
		$steps = $this->steps(); # 
		$format = 'd/m/Y | H:i:s'; # date format

		$c1=code2utf(0x2588);
		$c2=code2utf(0x2589);
		$c = $c2;

		$red='style="color:red;"';
		$green='style="color:green;"';
		
		for($d=1 ; $d <=5 ; $d++){
			
			echo "$days[$d]\n";
			for($i=0 ; $i < $steps ; $i++){
				if($i%$this->granularity() == 0)
						printf('%02d ', intval($i/$this->granularity())+$start);
				else
						echo "   ";
			
				foreach($this->calendars as $cc=>$cal){

					$pos = $start * $this->granularity() + $i;
					$ev = $cal->get_data($d, $pos);
					if($ev !== false){
						printf("<span $red title='%s : %s / %s / %s'>$c</span>", $cal->name(), $ev->start($format), $ev->length_as_text(), htmlspecialchars($ev->summary(), ENT_QUOTES));
					}
					else
						printf("<span title='%s' $green>$c</span>", $cal->name());
				}
				
				if($i%$this->granularity() == 0) echo "</b>";
				echo "\n";
			}
			
			echo "\n";
		}
	}
}
?>
