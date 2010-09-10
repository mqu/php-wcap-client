<?php

/*

	Site : DTI-Toulouse
	Caractéristique : Vidéo
	Capacité : 18
	Description : Salle de réunion Marchés - Accès réservé 1M - Bât. S - 1er étage - Pièce S113 - Tél. : 44 13
	Gestionnaires : Patricia RIBAL, Christine CHAMAYOU, Samuel MACARI, ...

*/

class RoomInfo{

	protected $info;
	protected $cn;

	public function __construct($name, $info){

		$this->name = $name;
		$this->info = array();

		$lines = split("\n", $info);
		foreach($lines as $line){
			$line = trim($line);

			if(preg_match('/(.+?)\s*:\s*(.+)/', $line, $values)){
				$this->info[trim(strtolower($values[1]))] = trim($values[2]);
			}
		}
	}
	public function name(){
		return $this->name;
	}

	public function get($name, $default=null){
		$name = strtolower($name);
		if(isset($this->info[$name]))
			return $this->info[$name];
		return $default;
	}

	public function capacity(){
		return $this->get('capacité');
	}

	public function type(){
		return $this->get('caractéristique');
	}

	public function site(){
		return $this->get('site');
	}

	public function gestionnaires(){
		return $this->get('gestionnaires');
	}

	public function type_short(){
		switch($this->type()){
			case 'Amphithéatre':   return 'APH';
			case 'Formation':      return 'FOR';
			case 'Formation PC':   return 'FOR';
			case 'Vidéo':          return 'VID';
			case 'Visio':          return 'VIS';
			case 'Réunion':        return 'REU';
			case 'Audio':          return 'AUD';
			return $this->type();

		}
	}

	public function keys(){
		return array_keys($this->info);
	}

	public function __toString(){
		$txt = $this->name() . "\n";;
		foreach($this->info as $key=>$val)
			$txt .= "   $key = $val\n";

		return $txt;
	}

	public function toHtml(){
		$txt = '';
		foreach($this->info as $key=>$val)
			$txt .= "<b>$key</b> = $val<br>\n";

		return $txt;
	}
}

/*
$content = 'Site : DTI-Toulouse
Caractéristique : Vidéo
Capacité : 18
Description : Salle de réunion Marchés - Accès réservé 1M - Bât. S - 1er étage - Pièce S113 - Tél. : 44 13
Gestionnaires : Patricia RIBAL, Christine CHAMAYOU, Samuel MACARI, ...
';

$info = new RoomInfo($content);

echo $info->capacity();

*/

?>
