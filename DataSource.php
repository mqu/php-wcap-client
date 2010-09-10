<?php


# a random data source for HtmlTable (HtmlCalendarDay).
# not used is real world.
#
class DataSource {
	public function __construct(){
	}
	
	public function get_data($row, $col){
		$class = rand(0,1)==1?'red':'green';
		
		$content = array(
			'title'   => "($row, $col)",
			'value' => "($row, $col)",
			'class'   => $class
		);

		return $content;

	}
}


?>
