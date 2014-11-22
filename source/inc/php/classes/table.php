<?php
class Table extends Pixyt{
	private $content = array();
	private $even = false;
	private $visualize = false;
	private $head = array();
	private $foot = array();
	private $rows = array();
	private $colgroups = array();
	public $lines = 0;
	
	function __construct($class = 'stdTable',$id='',$caption='',$rows=array()){
		if(empty($id)){$id = randStr(5);}
		if($class == 'visualize'){
			$this->visualize=true;
			T::$jsincludes[] = 'visualize.jQuery.js';
			T::$jsfoot[] = '$("#'.$id.'").visualize({"type":"line","width":700,"height":320,"lineWeight":7,"colors":["#3AE","#EA3","#AE3"]});';
			$class = 'hidden';
		}
		$this->rows = $rows;
		$this->head[] = '<table class="'.$class.'" id="'.$id.'">';
		if(!empty($caption)){
			$this->head[] = '<caption>'.$caption.'</caption>';
		}
	}
	
	public function addHeader($fields = '', $content = '', $xtra = ''){
		$this->head[] = $this->returnHeader($fields,$content,$xtra);
	}
	
	public function returnHeader($fields = '', $content = '', $xtra = ''){
		$content='';
		if(is_array($fields)){
			foreach ($fields as $field){
				$content .= $this->returnTh($field);
			}
		}
		return '<thead'.$xtra.'>'.$content.'</thead>';
	}
	
	public function returnTh($content){
		return '<th>'.$content.'</th>';
	}
	
	public function colgroups($styles = ''){
		if(is_array($styles)){
			foreach ($styles as $i=>$style){
				$this->head[] = '<colgroup style="'.$style.'" class="col'.$i.'"></colgroup>';
			}
		}
	}
	
	public function addLine($fields = '', $class = '', $xtra =''){
		$this->content[] = $this->returnLine($fields, $class,$xtra);
	}
	
	public function insert($content){
		$this->content[] = $content;
	}
	
	public function returnLine($fields = '', $class = '', $xtra =''){
		$content = '';
		if(is_array($fields)){
			if(isset($this->rows[$this->lines])){
				$content .= '<th scope="row">'.$this->rows[$this->lines].'</th>';
			}
			foreach ($fields as $target=>$field){
				$content .= $this->returnTd($field,$xtra);
			}
		}
		else{$content .= $this->returnTd('NOT AN ARRAY');return false;}
		if($this->even){$class .= ' even';}else{$class .= ' uneven';}
		$this->even = !$this->even;
		$this->lines++;
		return '<tr class="'.$class.'" '.$xtra.'>'.$content.'</tr>';
	}
	
	public function returnTd($content,$xtra=''){
		return '<td '.$xtra.'>'.$content.'</td>';
	}
	
	public function printContent(){
		T::$body[] = $this->returnTable();
	}
	
	public function returnContent(){
		return $this->returnTable();
	}
	
	public function printTable(){
		T::$body[] = $this->returnTable();
	}
	
	public function returnTable(){
		return implode('',$this->head).'<tbody>'.implode('',$this->content).'</tbody>'.implode('',$this->foot).'</table>';
	}

}
?>