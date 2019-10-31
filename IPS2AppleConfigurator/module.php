<?
    // Klassendefinition
    class IPS2AppleConfigurator extends IPSModule 
    {
	    
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
		$this->ConnectParent("{715318DA-1FA4-3CB4-2F0C-383322125646}");
		
        }
 	
	public function GetConfigurationForm() 
	{ 
		$arrayStatus = array(); 
		$arrayStatus[] = array("code" => 101, "icon" => "inactive", "caption" => "Instanz wird erstellt"); 
		$arrayStatus[] = array("code" => 102, "icon" => "active", "caption" => "Instanz ist aktiv");
		$arrayStatus[] = array("code" => 104, "icon" => "inactive", "caption" => "Instanz ist inaktiv");
		$arrayStatus[] = array("code" => 202, "icon" => "error", "caption" => "Kommunikationfehler!");
				
		$arrayElements = array(); 
		
		$arraySort = array();
		$arraySort = array("column" => "Device", "direction" => "ascending");
		
		$arrayColumns = array();
		$arrayColumns[] = array("caption" => "Modell", "name" => "Device", "width" => "100px", "visible" => true);
		$arrayColumns[] = array("caption" => "Name", "name" => "Name", "width" => "auto", "visible" => true);
		
		$StationArray = array();
		If ($this->HasActiveParent() == true) {
			$DevicenArray = unserialize($this->GetData());
		}
		$arrayValues = array();
		for ($i = 0; $i < Count($StationArray); $i++) {
			$arrayCreate = array();
			$arrayCreate[] = array("moduleID" => "{4C40D461-8047-04BC-3566-52E76067225A}", 
					       "configuration" => array("DeviceID" => $DeviceArray[$i]["DeviceID"]));
			$arrayValues[] = array("Device" => $DeviceArray[$i]["DeviceModel"], "Name" => $DeviceArray[$i]["DeviceName"], 
					       "instanceID" => $DeviceArray[$i]["InstanceID"], 
					       "create" => $arrayCreate);
		}
		
		$arrayElements[] = array("type" => "Configurator", "name" => "AppleDevices", "caption" => "Apple-Devices", "rowCount" => 10, "delete" => false, "sort" => $arraySort, "columns" => $arrayColumns, "values" => $arrayValues);
		
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
 	}       
	   
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
            	// Diese Zeile nicht löschen
            	parent::ApplyChanges();
		
		If (IPS_GetKernelRunlevel() == 10103) {	
			If ($this->HasActiveParent() == true) {
				$this->SetStatus(102);
			}
			else {
				$this->SetStatus(104);
			}
		}
	}
	    
	// Beginn der Funktionen
	private function GetData()
	{
		
		$DeviceArray = array();
		
		$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{485663CC-3911-FAC7-9FCC-6E4D06438527}", 
				 "Function" => "getConfiguratorData")));
		If ($Result <> false) {
			$this->SetStatus(102);
			$this->SendDebug("GetData", $Result, 0);

			$ResultArray = array();
			$ResultArray = unserialize($Result);
			$i = 0;
			for ($i = 0; $i <= count($ResultArray); $i++) {
				$ResultArray[$i]["InstanceID"] = $this->GetDeviceInstanceID($ResultArray[$i]["DeviceID"]);
			}
		}
		else {
			$this->SetStatus(202);
			$this->SendDebug("GetData", "Fehler bei der Datenermittlung!", 0);
		}
		
	return $DevicenArray;
	}
	
	function GetDeviceInstanceID(string $DeviceID)
	{
		$guid = "{4C40D461-8047-04BC-3566-52E76067225A}";
	    	$Result = 0;
	    	// Modulinstanzen suchen
	    	$InstanceArray = array();
	    	$InstanceArray = @(IPS_GetInstanceListByModuleID($guid));
	    	If (is_array($InstanceArray)) {
			foreach($InstanceArray as $Module) {
				If (strtolower(IPS_GetProperty($Module, "DeviceID")) == strtolower($DeviceID)) {
					$this->SendDebug("GetDeviceInstanceID", "Gefundene Instanz: ".$Module, 0);
					$Result = $Module;
					break;
				}
				else {
					$Result = 0;
				}
			}
		}
	return $Result;
	}
}
?>
