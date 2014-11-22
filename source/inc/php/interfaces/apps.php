<?php
class Apps extends Interfaces{
	public function directory(){
		$r = '';
		$r .= dv('d1000 center');
		$r .= dv('Lightroom').'<h2><span class="blckbgd white padded">Lighroom extension</p></h2>';
		$r .= dv('d2 col centerText').'<img src="/img/apps/lightroom/lightroom-520x520.png" style="margin:0 20%;width:60%;" alt="Lightroom"/>'.'<p>'.xtlnk('http://pixyt.com/download.php?app=lightroom','<img src="/img/download/100.png" width="50" height="50"/> Download',array('class'=>'grey')).'</p>'.xdv();
		$r .= dv('d2 col').'<h3>'.translate('The Lightroom App allows you to upload photos to Pixyt, straight from Lightroom.').'</h3>'.'<p class="quote padder">'.translate('Wether you use Windows or Mac, simply download the extension and install it and you will be able to Export photos to your  Pixyt account. To do so, edit your photo then simply press Export (cmd+shift+e) then select Pixyt Export from the drop down menu on top of the window.').'</p>'.'<ul><li>'.translate('1. Download the extension').'</li><li>'.translate('2. Unzip it').'</li><li>'.translate('3. Copy pixyt.lrplugin to:').'<ol class="tiny squares"><li>Mac OSX  ~/Library/Application Support/Adobe/Lightroom/Modules</li><li>Windows 7  C:\Users\username\AppData\Roaming\Adobe\Lightroom\Modules</li></ol></li><li>4. '.translate('Open Lightroom').'</li><li>5. '.translate('Select File > Plug-in Manager').'</li><li>6. '.translate('Click Add').'</li><li>7. '.translate('Select the LRplugin where you just pasted it').'</li><li>8. '.translate('Done!').'</li></ul>'.xdv();
		$r .= xdv();
		$r .= xdv();
		return $r;
	}
	
	protected function lightroom(){
		$r = '';
		$r .= '';
		return $r;
	}
}
?>