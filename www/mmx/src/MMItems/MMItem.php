<?php

/**
 * @property-read int  Timestamp
 * @property-read int  Found
 */
abstract class MMItem {

	protected $_Timestamp = null;
	protected $_Found = false;

	public abstract function GetKey();
	public final function ToJson() { return json_encode($this->ToArray()); }
	public abstract function ToArray();

	protected abstract function LoadFromTMDb();
	protected abstract function LoadFromFile();
	protected abstract function SaveIntoFile();
	public final function Load($force = false) {
		if ($this->_Timestamp === null || $force) {
			$this->_Found = false;
			$this->_Timestamp = XDateTime::Now()->AsInt();
			if (!$force) $this->_Found = $this->LoadFromFile();
			if (!$this->_Found) {
				$this->_Found = $this->LoadFromTMDb();
				if ($this->_Found) $this->SaveIntoFile();
			}
		}
		return $this;
	}


	public function __get($name) { $this->Load(); return $this->{'_'.$name}; }



	public abstract function GetDataPath();
	public final function HasDataFile(){ return file_exists($this->GetDataPath()); }


}