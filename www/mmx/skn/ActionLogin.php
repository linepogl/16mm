<?php

class ActionLogin extends MMAction {

	public function IsPermitted(){ return true; }
	public function RenderJavascript(){
		echo "window.mmx.ClearMain();";
		echo "window.mmx.ShowLogin();";
	}

}