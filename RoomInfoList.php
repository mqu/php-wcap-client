<?php

require_once('RoomInfo.php');
require_once('Ldap.php');
require_once('LdapAngelique.php');

/**
 * gestion d'une liste de salles de réunion
 *  - extraction depuis annuaire LDAP,
 *  - selection par nom, capacité, fonction
 *  - la selection par capacité supporte les opérateurs : ==, >=,
 *  - obtention de la liste des noms, des capacités (pour menus)
 */

class RoomInfoList{

	protected $list;

	public function __construct(){
		$this->list = array();
	}

	public function add($name, $room){
		$this->list[$name] = $room;
	}


	public function set_rooms_from_ldap($ldap, $filter=null){
	
		global $config;

		$filter_expr = $config['app/module/salle/filter']; # filtre permettant d'obtenir un nom court sur les salles

		$ldap->set_sort_attribute('sn');

		if($filter != null){
			$filter = "*$filter*";
			$filter = str_replace('**', '*', $f);
			$filter = sprintf("(&%s(cn=%s))", $config['angelique/filter/salle'], $filter);
		}
		else
			$filter = sprintf("(&%s)", $config['angelique/filter/salle']);

		$base = $config['angelique/dn/site'];
		$list = $ldap->raw_search($base, $filter, array('cn', 'description', 'givenName', 'sn', 'mail'));

		$this->list = array();
		foreach($list as $entry){
			$sn = $entry->sn();

			# DSNA-DTI-MND-SalleA006-rs
			if(preg_match("/${filter_expr}/", $sn, $values)){
				$sn = $values[2];
			}
			$sn = strtolower($sn);

			$this->add($sn, new RoomInfo($sn, $entry->get_attrib('description')));
		}

		return $this->list;
	}

	public function get_by_name($name){
		if(isset($this->list[$name]))
			return $this->list[$name];
		return null;
	}

	public function get_by_capacities(){
		$list = array();
		foreach($this->list as $room)
			$list[$room->capacity()][] = $room->name();

		return $list;
	}

	public function get_capacities(){
		$list = array();
		foreach($this->list as $room)
			$list[] = $room->capacity();
		sort($list);
		return array_values(array_unique($list));
	}

	public function get_names(){
		$list = array();
		foreach($this->list as $room)
			$list[] = $room->name();
		sort($list);

		return array_values(array_unique($list));
	}

	public function get_names_with_filter($filter){
		$list = array();

		if(!isset($filter['capacity']))
			$filter['capacity'] = 1;

		if(!isset($filter['operator']))
			$filter['operator'] = '>=';

		$rooms = $this->select_by_capacity($filter['capacity'], $filter['operator']);

		if(!isset($filter['type']))
			$filter['type'] = null;

		foreach($rooms as $room){
			if($filter['type'] == null)
				$list[] = $room->name();
			elseif($filter['type'] == $room->type())
				$list[] = $room->name();
		}

		sort($list);

		return array_values(array_unique($list));

	}

	public function get_types(){
		$list = array();
		foreach($this->list as $room)
			$list[] = $room->type();
		sort($list);
		return array_values(array_unique($list));
	}

	public function get_by_types(){
		$list = array();
		foreach($this->list as $room)
			$list[$room->type()][] = $room->name();
		return $list;
	}

	public function select_by_capacity($size, $op='>='){
		$list = array();

		foreach($this->list as $key=>$room){
			switch($op){
				case '==':  
				case '=':  
					if($room->capacity() == $size) $list[] = $room; break;

				case '>':   if($room->capacity() >  $size) $list[] = $room; break;
				case '<':   if($room->capacity() <  $size) $list[] = $room; break;
				case '>=':  if($room->capacity() >= $size) $list[] = $room; break;
				case '<=':  if($room->capacity() <= $size) $list[] = $room; break;

				default:
					throw new Exception ("operateur '$op'non supporté");
			}
		}

		return $list;
	}

	public function select_by_type($type){
		$list = array();

		foreach($this->list as $key=>$room){
			if($room->type() == $type)
				$list[] = $room;
		}

		return $list;
	}

	public function __toString(){
		$txt = '';
		foreach($this->list as $room)
			$txt .= $room->__toString();

		return $txt;
	}
}

?>
