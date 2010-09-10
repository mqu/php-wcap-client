<?php


class UserInfo{

	protected $email;
	protected $initials;

	public function __construct($email, $initials=null){
		$this->email = $email;
		$this->initials = $initials;

		if($this->initials == null){
			if(preg_match('/(.+)\.(.+)@.*/', $this->email, $values)){
				# echo "mail = {$this->email} ; prenom = $prenom ; nom = $nom<br>\n";
				$this->initials = strtoupper($values[1][0] . $values[2][0]);
			}
			elseif(preg_match('/(.+) (.+)/', $this->email, $values)){
				$this->initials = strtoupper($values[1][0] . $values[2][0]);
			}
		}
	}

	public function __toString(){
		return $this->email;
	}

	public function toHtml(){
		return $this->email;
	}

	public function initials(){
		return $this->initials;
	}
}


?>
