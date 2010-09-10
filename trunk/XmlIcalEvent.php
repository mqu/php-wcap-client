<?php

class XmlIcalEvent extends XmlWcapResponse {
	public function summary(){
		if($this->_class() == 'PRIVATE')
			return 'PRIVE';

		return trim($this->get_field('SUMMARY'));
	}
	public function uid(){
		return $this->get_field('UID');
	}
	public function recurid(){
		return $this->get_field('RECURID');
	}
	public function start($format=null){
		if($format == null)
			return Ical::iCal2Timestamp($this->get_field('START'));
		else
			return date($format, Ical::iCal2Timestamp($this->get_field('START')));
	}

	public function end($format=null){
		if($format == null)
			return Ical::iCal2Timestamp($this->get_field('END'));
		else
			return date($format, Ical::iCal2Timestamp($this->get_field('END')));
	}

	public function length(){
		return $this->end() - $this->start();
	}

	public function length_in_hours(){
		return intval($this->length() / (60*60));
	}

	public function length_in_days(){
		return intval($this->length_in_hours() / 24);
	}

	public function length_in_week(){
		return intval($this->length_in_days() / 7);
	}

	public function length_in_month(){
		return intval($this->length_in_days() / 30);
	}

	# output HH:mn:ss
	public function length_as_text($format='HH:mm'){
		$len = intval($this->length()/60);
		$h = intval($len / 60);
		$m = intval($len % 60);
		if($format == 'HH:mm'){
			return sprintf('%02d:%02d', $h, $m);
		}
	}

	public function _class(){
		return $this->get_field('CLASS');
	}
	public function transp(){
		return $this->get_field('TRANSP');
	}

	public function organizer(){
		return $this->get_field('ORGANIZER');
	}
}

?>
