<?php

abstract class MMAction extends Action {
//	public function GetDefaultMode(){ return Action::MODE_HTML_FRAGMENT; }
	public function IsPermitted(){ return true; }

	public function Render(){


//		$c = Chain::Search('test');
//		$c = Movie::Search('test');
//		dump(Movie::Find(100)->Load());
//		dump($c,null);
//		dump(TMDb::GetChainInfo(44652),null);
//		die;
		//dump(sprintf('%08X',13000000));
		//$c = TMDb::GetMovieInfo(100);
		//dump($c);



		echo Js::BEGIN;
		if ($this->mode === Action::MODE_NORMAL) {
			echo "jQuery(document).ready(function(){";
			echo "window.mmx.loading_page = true;";
			$this->RenderJavascriptInitialState();
			$this->RenderJavascript();
			echo "window.mmx.loading_page = false;";
			echo "});";
		}
		else {
			echo "window.mmx.ClearMain();";
			$this->RenderJavascript();
		}
		echo Js::END;
	}
	public function RenderJavascriptInitialState() {
		echo "window.mmx.OpenTab(".new Js($this->GetName()).",".new Js($this->GetHrefPlain()).",true);";
	}
	public function RenderJavascript(){}

}