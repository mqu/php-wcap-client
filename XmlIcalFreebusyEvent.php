<?php


/*

<?xml version="1.0"?>
<FB FBTYPE="BUSY">20100802T080000Z/20100802T090000Z</FB>

*/

class XmlIcalFreebusyEvent extends XmlIcalEvent {
	protected $start;
	protected $end;

	public function __construct($xml){
		parent::__construct($xml);

		$list = split('/', (string) $this->xml);
		if(count($list) != 2) throw new Exception ('mauvais format de données XML.');
		$this->start = Ical::iCal2Timestamp($list[0]);
		$this->end   = Ical::iCal2Timestamp($list[1]);
	}

	public function summary(){
		return 'occupé';
	}

	public function uid(){
		throw new Exception('not implemented in XmlIcalFreebusyEvent');
	}
	public function recurid(){
		throw new Exception('not implemented in XmlIcalFreebusyEvent');
	}
	public function _class(){
		throw new Exception('not implemented in XmlIcalFreebusyEvent');
	}
	public function transp(){
		return 'OPAQUE';
	}

	public function type(){
		return (string) $this->xml[FBTYPE];
	}

	public function start($format=null){
		if($format == null)
			return $this->start;
		else
			return date($format, $this->start);
	}
	public function end($format=null){
		if($format == null)
			return $this->end;
		else
			return date($format, $this->end);
	}
}



?>
