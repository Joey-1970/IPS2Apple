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
			$arrayValues[] = array("Brand" => $DeviceArray[$i]["Device"], "Name" => $DeviceArray[$i]["Name"], 
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
		$locationObject = json_decode($this->ReadPropertyString('Location'), true);
		$Lat = $locationObject['latitude'];
		$Long = $locationObject['longitude']; 
		$Radius = $this->ReadPropertyFloat("Radius");
		$Radius = min(25, max(0, $Radius));
		$StationArray = array();
		If (($Lat <> 0) AND ($Long <> 0) AND ($Radius > 0)) {
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{6ADD0473-D761-A2BF-63BE-CFE279089F5A}", 
				"Function" => "GetAreaInformation", "InstanceID" => $this->InstanceID, "Lat" => $Lat, "Long" => $Long, "Radius" => $Radius )));
			If ($Result <> false) {
				$this->SetStatus(102);
				$this->SendDebug("GetData", $Result, 0);
				//$this->ShowResult($Result);
				$ResultArray = array();
				$ResultArray = json_decode($Result);
				// Fehlerbehandlung
				If (boolval($ResultArray->ok) == false) {
					$this->SendDebug("ShowResult", "Fehler bei der Datenermittlung: ".utf8_encode($ResultArray->message), 0);
					return;
				}
				
				$i = 0;
				foreach($ResultArray->stations as $Stations) {
					$StationArray[$i]["Brand"] = ucwords(strtolower($Stations->brand));
					$StationArray[$i]["Name"] = ucwords(strtolower($Stations->name));
					$StationArray[$i]["Street"] = ucwords(strtolower($Stations->street));
					$StationArray[$i]["Place"] = ucwords(strtolower($Stations->place));
					$StationArray[$i]["StationsID"] = $Stations->id;
					$StationArray[$i]["InstanceID"] = $this->GetStationInstanceID($Stations->id);
					$i = $i + 1;
				}
				$this->SendDebug("GetData", "TankstellenArray: ".serialize($StationArray), 0);
				
			}
			else {
				$this->SetStatus(202);
				$this->SendDebug("GetData", "Fehler bei der Datenermittlung!", 0);
			}
		}
		else {
			$this->SendDebug("GetDataUpdate", "Keine Koordinaten verfügbar!", 0);
		}
	return serialize($StationArray);
	}
	
	function GetStationInstanceID(string $StationID)
	{
		$guid = "{47286CAD-187A-6D88-89F0-BDA50CBF712F}";
	    	$Result = 0;
	    	// Modulinstanzen suchen
	    	$InstanceArray = array();
	    	$InstanceArray = @(IPS_GetInstanceListByModuleID($guid));
	    	If (is_array($InstanceArray)) {
			foreach($InstanceArray as $Module) {
				If (strtolower(IPS_GetProperty($Module, "StationID")) == strtolower($StationID)) {
					$this->SendDebug("GetStationInstanceID", "Gefundene Instanz: ".$Module, 0);
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
