<?php

class XmlIcal extends XmlWcapResponse {

	public function get_icals(){
		$list = $this->xpath('//iCal');
		$list2=array();
		foreach($list as $xml)
			$list2[] = new XmlIcal($xml->asXML());
		return $list2;
	}

	public function name(){
		return $this->get_field('X-NSCP-CALPROPS-NAME');
	}

	public function primary_owner(){
		return $this->get_field('X-NSCP-CALPROPS-PRIMARY-OWNER');
	}

	public function short_name(){
		$name = $this->name();

		$name = str_replace('@aviation-civile.gouv.fr', '', $name);
		$name = strtoupper($name);
		$name = str_replace('DSNA-DTI-MND-SALLE', '', $name);
		$name = str_replace('-RS', '', $name);

		return $name;
	}

	public function id(){
		return $this->get_field('X-NSCP-CALPROPS-RELATIVE-CALID');
	}

	# if $auto == true, then auto join freebusy events
	public function events($auto = true){
		$list = $this->xml->xpath("//EVENT");
		$records = array();
		$list2 = array();
		foreach($list as $entry){
			$list2[] = new XmlIcalEvent($entry->asXML());
		}
		if(!$auto)
			return $list2;
		return array_merge($list2, $this->freebusy_list());
	}

	public function freebusy_list(){
		$list = $this->xml->xpath("//FREEBUSY/FB");
		$records = array();
		$list2 = array();
		foreach($list as $entry){
			$list2[] = new XmlIcalFreebusyEvent($entry->asXML());
		}
		return $list2;
	}
}

/**

	sample usage for XmlIcal, XmlIcalEvent.

	$ical = new XmlIcal($xml));
	
	$icals = $ical->get_icals();
	foreach($icals as $cal){
		echo $cal->name() . "\n";
		foreach($cal->events() as $ev){
			printf("  %s : %s : %s\n", $ev->start(), $ev->end(), $ev->summary());
		}
	}

*/

?>
