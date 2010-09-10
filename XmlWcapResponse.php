<?php

class XmlWcapResponse {
	protected $xml;

	public function __construct($xml){
		$this->xml = new SimpleXMLElement($xml);
	}

	public function errno(){
		return $this->get_field('X-NSCP-WCAP-ERRNO');
	}

	public function calendar_id(){
		return $this->get_field('X-NSCP-WCAP-CALENDAR-ID');
	}

	public function session_id(){
		return $this->get_field('X-NSCP-WCAP-SESSION-ID');
	}

	public function user_id(){
		return $this->get_field('X-NSCP-WCAP-USER-ID');
	}

	public function get_field($name){
		$list = $this->xml->xpath("//$name");
		if(count($list) == 0)
			return false;
		return (string) $list[0];
	}

	public function xpath($query){
		return $this->xml->xpath($query);
	}

	public function asXml(){
		return $this->xml->asXml();
	}
}

?>
