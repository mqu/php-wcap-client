<?php

class HtmlCalendarDay extends HtmlTable {
	protected $data_source = null;

	public function __construct($w, $h, $class='calendar', $config=array()){
		parent::__construct($w, $h, $class);

		# default config values
		$this->config = array(
			'start' => 8,
			# 'end' => 18,
			'granularity' => 4
		);

		foreach($config as $key=>$val){
			$this->config[$key] = $val;
		}
	}

	public function start(){
		return $this->config['start'];
	}

	# number of units for an hour : can be 1, 2, 4
	public function granularity(){
		return $this->config['granularity'];
	}

	protected function table_start(){
		parent::table_start();
		$this->count = -1;
	}

	protected function row_start(){
		echo "<tr>\n";

		# table header
		if($this->count == -1){
			if($this->granularity() != 1)
				$colspan = 'colspan="2"';
			else
				$colspan = '';

			$info = array(
				"salle"    => "nom de la salle",
				"capacité" => "capacité d'accueil",
				"fonction" => "Audio, Visio, Réunion"
			);
			$html = '';
			foreach($info as $key=>$help)
				$html .= sprintf("<span title='%s'>%s</span><br>\n", htmlspecialchars($help, ENT_QUOTES), $key);
			printf("<td class='th' $colspan valign='top'>$html</td>");

			$this->count++;
			return;
		}

		$hour = sprintf('%02d', $this->start() + intval($this->count/$this->granularity()));
		$mn = intval($this->count % $this->granularity()) * (60 / $this->granularity());

		$h_class = 'hour';
		$m_class = 'mn';

		# if($hour == 12 || $hour == 13){
		#	$h_class = 'grey';
		#	$m_class = 'grey';
		#}

		switch($this->granularity()){
		case 1:
			if($this->count % $this->granularity() == 0)
				printf("<td class='%s' valign='top'>$hour</td>", $h_class, $this->granularity());
			break;

		case 2:
		case 4:
		case 6:
		case 8:
			if($this->count % $this->granularity() == 0)
				printf("<td class='%s' rowspan='%s' valign='top'>$hour</td>", $h_class, $this->granularity());
			printf("<td class='%s'>%02d</td>", $m_class, $mn);
			break;
		}

		$this->count++;
	}
	
	public function set_data_source($source){
		$this->data_source = $source;
	}
	
	protected function get_cell_content($row, $col, $user_data=null){
		if(isset($this->data_source))
			return $this->data_source->get_data($row, $col, $user_data);
		return "($row:$col)"; # should not be here.
	}
	protected function draw_cell($col, $row, $user_data=null){
		$content = $this->get_cell_content($row, $col, $user_data);
		
		printf("<td title='%s' class='%s'>%s</td>", htmlspecialchars($content['title'], ENT_QUOTES), $content['class'], $content['value']);

	}	
}
?>
