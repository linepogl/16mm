<?php

abstract class MMItem {

	public abstract function GetKey();
	public final function ToJson() { return json_encode($this->ToArray()); }
	public abstract function ToArray();

	private $ok = false;
	private $needs_loading = true;
	public function NotFound() { $this->Load(); return !$this->ok; }
	protected abstract function OnLoad();
	public final function Load($force = false) {
		if ($this->needs_loading || $force) {
			$this->ok = $this->OnLoad();
			$this->needs_loading = false;
		}
		return $this;
	}


	public function __get($name) { $this->Load(); return $this->{'_'.$name}; }

}