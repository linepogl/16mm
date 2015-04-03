<?php

/**
 * @property-read int  Timestamp
 * @property-read int  Found
 */
abstract class MMItem {

	protected $_Timestamp = null;
	protected $_Found = false;

	public function __get($name) { $this->Load(); return $this->{'_'.$name}; }

	public abstract function GetKey();
	public abstract function ToArray();
	public final function ToJson() { return json_encode($this->ToArray()); }


	/** @return string */ protected abstract function GetCouchUrl();
	/** @return array */  protected abstract function GetTMDbInfo();
	/** @return bool  */  protected abstract function LoadInfo($info);

	public final function Load($force = false) {
		if ($this->_Timestamp === null || $force) {
			$this->_Found = false;
			$this->_Timestamp = XDateTime::Now()->AsInt();

			$couch_url = $this->GetCouchUrl();

			$info1 = null;
			$q = CouchDB::Load($couch_url);
			if ($q->IsOK()) $info1 = $q->GetResultArray();
			if ($info1 !== null && !$force) {
				$this->_Found = $this->LoadInfo($info1);
			}

			if (!$this->_Found) {
				$info2 = $this->GetTMDbInfo();
				if ($info2 !== null) {
					if ($info1 !== null) $info2['_id'] = $info1['_id'];
					$this->_Found = $this->LoadInfo($info2);
					if ($this->_Found) {
						if ($info1 !== null) $info2['_rev'] = $info1['_rev'];
						CouchDB::Save($couch_url,$info2);
					}
				}
			}
		}
		return $this;
	}


}