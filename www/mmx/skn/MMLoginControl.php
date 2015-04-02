<?php

class MMLoginControl extends LoginControl {

	public function Render(){
		(new ActionLogin())->WithMode(Oxygen::GetActionMode())->Render();
	}

}