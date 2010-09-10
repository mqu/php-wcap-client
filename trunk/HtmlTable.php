<?php

class HtmlTable {
	protected $width;
	protected $height;
	protected $class;  # table class name

	public function __construct($w, $h, $class='sample'){
		$this->width = $w;
		$this->height = $h;
		$this->class = $class;
	}

	public function render($user_data){
		$this->table_start();
		$this->header();

		$this->row_start();

		# header (col_id = -1)
		for($i=0 ; $i<$this->width ; $i++)
			$this->draw_cell($i, -1, $user_data);

		$this->row_end();

		for($j=0 ; $j< $this->height ; $j++){

			$this->row_start();
			for($i=0 ; $i<$this->width ; $i++)
				$this->draw_cell($i, $j, $user_data);
			$this->row_end();
		}
		$this->table_end();
	}
	
	protected function header(){
		# echo "<table>\n";
	}
	protected function table_start(){
		printf("<table class='%s'>\n", $this->class);
	}
	protected function table_end(){
		echo "</table>\n";
	}
	
	protected function draw_cell($i,$j, $user_data=null){
		printf("<td>%s</td>\n", $this->get_cell_content($i, $j, $user_data));
	}	
	protected function row_start(){
		echo "<tr>\n";
	}
	protected function row_end(){
		echo "</tr>\n";
	}
	protected function get_cell_content($row, $col){
		return "$row:$col";
	}
}
?>
