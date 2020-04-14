<?
    // Klassendefinition
    class IPS2AppleDevice extends IPSModule 
    {
	
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
		$this->ConnectParent("{715318DA-1FA4-3CB4-2F0C-383322125646}");
		$this->RegisterPropertyString("DeviceID", "Apple Device ID");
		
		// Profil anlegen
		$this->RegisterProfileBoolean("JaNein.IPS2Apple", "Information");
		IPS_SetVariableProfileAssociation("JaNein.IPS2Apple", 0, "Nein", "", -1);
		IPS_SetVariableProfileAssociation("JaNein.IPS2Apple", 1, "Ja", "", -1);
		
		$this->RegisterProfileFloat("Meter.IPS2Apple", "Distance", "", " m", 0, 1000, 0.1, 1);
		
		$this->RegisterProfileFloat("Percent.IPS2Apple", "Graph", "", " %", 0, 100, 0.1, 1);
		
		// Statusvariablen anlegen
		$this->RegisterVariableInteger("LastUpdate", "Letztes Update", "~UnixTimestamp", 10);
		
		$this->RegisterVariableString("modelDisplayName", "Model Name", "", 20);
		
		$this->RegisterVariableFloat("batteryLevel", "Batterie Level", "Percent.IPS2Apple", 30);
		$this->RegisterVariableString("batteryStatus", "Batterie Status", "", 40);
		$this->RegisterVariableString("name", "Device Name", "", 50);
		$this->RegisterVariableString("deviceClass", "Device Klasse", "", 60);
		
		$LocationPostion = 100;
		$this->RegisterVariableBoolean("isOld", "ist alt", "JaNein.IPS2Apple", $LocationPostion);
		$this->RegisterVariableBoolean("isInaccurate", "ist Inakkurat", "JaNein.IPS2Apple", $LocationPostion + 10);
		$this->RegisterVariableFloat("Altitude", "Altitude", "Meter.IPS2Apple", $LocationPostion + 20);
		$this->RegisterVariableString("positionType", "Position Typ", "", $LocationPostion + 30);
		$this->RegisterVariableFloat("Latitude", "Latitude", "", $LocationPostion + 40);
		$this->RegisterVariableInteger("floorLevel", "Stockwerk", "", $LocationPostion + 50);
		$this->RegisterVariableFloat("horizontalAccuracy", "Horizontale Genauigkeit", "Meter.IPS2Apple", $LocationPostion + 60);
		$this->RegisterVariableString("locationType", "Lokation Typ", "", $LocationPostion + 70);
		$this->RegisterVariableInteger("timeStamp", "Zeitstempel der Lokalisierung", "~UnixTimestamp", $LocationPostion + 80);
		$this->RegisterVariableBoolean("locationFinished", "Messung abgeschlossen", "JaNein.IPS2Apple", $LocationPostion + 90);
		$this->RegisterVariableFloat("verticalAccuracy", "Vertikale Genauigkeit", "Meter.IPS2Apple", $LocationPostion + 100);
		$this->RegisterVariableFloat("Longitude", "Longitude", "", $LocationPostion + 110);
		$this->RegisterVariableString("GoogleMaps", "GoogleMaps", "~HTMLBox", $LocationPostion + 120);
        
        }
 	
	public function GetConfigurationForm() 
	{ 
		$arrayStatus = array(); 
		$arrayStatus[] = array("code" => 101, "icon" => "inactive", "caption" => "Instanz wird erstellt"); 
		$arrayStatus[] = array("code" => 102, "icon" => "active", "caption" => "Instanz ist aktiv");
		$arrayStatus[] = array("code" => 104, "icon" => "inactive", "caption" => "Instanz ist inaktiv");
		$arrayStatus[] = array("code" => 202, "icon" => "error", "caption" => "Kommunikationfehler!");
				
		$arrayElements = array(); 
		$arrayElements[] = array("type" => "ValidationTextBox", "name" => "DeviceID", "caption" => "Apple Device ID");
		
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "label" => "Test Center"); 
		$arrayElements[] = array("type" => "TestCenter", "name" => "TestCenter");

		
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
 	}       
	   
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
            	// Diese Zeile nicht löschen
            	parent::ApplyChanges();
		
		$this->RegisterMessage($this->InstanceID, 10103);
		
		//ReceiveData-Filter setzen
		$DeviceID = $this->ReadPropertyString("DeviceID");
		$Filter = '(.*"DeviceID":'.$DeviceID.'.*)';
		//$this->SetReceiveDataFilter($Filter);
		
		If ($this->HasActiveParent() == true) {
			$this->SetStatus(102);	
			$this->SendDataToParent(json_encode(Array("DataID"=> "{485663CC-3911-FAC7-9FCC-6E4D06438527}", 
					 "Function" => "getData")));
		}
		else {
			$this->SetStatus(104);
		}
		
	}
	
	public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    	{
 		switch ($Message) {
			case 10103:
				$this->ApplyChanges();
				break;
			
		}
    	}    
	    
	public function ReceiveData($JSONString) 
	{
	 	// Empfangene Daten vom Gateway/Splitter
	    	$data = json_decode($JSONString);
	 	switch ($data->Function) {
			case "set_State":
			    	//$this->SendDebug("RequestAction", "Ankommende ID:".$data->DeviceID, 0);
				If ($data->DeviceID == $this->ReadPropertyString("DeviceID")) {
				   	$this->ShowData($data->DeviceDataArray);
			   	}
			    break;
		}
	}    
	    
	// Beginn der Funktionen
	private function ShowData(string $DeviceData)
	{
		$DeviceDataArray = unserialize($DeviceData);
		$this->SetStatus(102);
		SetValueInteger($this->GetIDForIdent("LastUpdate"), time());
		SetValueString($this->GetIDForIdent("modelDisplayName"), $DeviceDataArray->modelDisplayName);
		SetValueString($this->GetIDForIdent("batteryStatus"), $DeviceDataArray->batteryStatus);
		If ($DeviceDataArray->batteryStatus <> "Unknown") {
			SetValueFloat($this->GetIDForIdent("batteryLevel"), $DeviceDataArray->batteryLevel * 100);
		}
		SetValueString($this->GetIDForIdent("name"), $DeviceDataArray->name);
		SetValueString($this->GetIDForIdent("deviceClass"), $DeviceDataArray->deviceClass);
		
		
		If (isset($DeviceDataArray->location)) {
			SetValueBoolean($this->GetIDForIdent("isOld"), $DeviceDataArray->location->isOld);
			SetValueBoolean($this->GetIDForIdent("isInaccurate"), $DeviceDataArray->location->isInaccurate);
			SetValueFloat($this->GetIDForIdent("Altitude"), $DeviceDataArray->location->altitude);
			SetValueString($this->GetIDForIdent("positionType"), $DeviceDataArray->location->positionType);
			SetValueFloat($this->GetIDForIdent("Latitude"), $DeviceDataArray->location->latitude);
			SetValueInteger($this->GetIDForIdent("floorLevel"), $DeviceDataArray->location->floorLevel);
			SetValueFloat($this->GetIDForIdent("horizontalAccuracy"), $DeviceDataArray->location->horizontalAccuracy);
			SetValueString($this->GetIDForIdent("locationType"), $DeviceDataArray->location->locationType);
			SetValueInteger($this->GetIDForIdent("timeStamp"), intval($DeviceDataArray->location->timeStamp / 1000));
			SetValueBoolean($this->GetIDForIdent("locationFinished"), $DeviceDataArray->location->locationFinished);
			SetValueFloat($this->GetIDForIdent("verticalAccuracy"), $DeviceDataArray->location->verticalAccuracy);
			SetValueFloat($this->GetIDForIdent("Longitude"), $DeviceDataArray->location->longitude);
			$this->GoogleMaps($DeviceDataArray->location->latitude, $DeviceDataArray->location->longitude);
        	} 
		$this->SendDebug("ShowData", serialize($DeviceDataArray), 0);
		//$this->SendDebug("ShowData", $DeviceDataArray->location->longitude, 0);
	}
	
	private function GoogleMaps(float $Latitude, float $Longitude)
	{
		$GoogleMapsInstanceID = $this->SendDataToParent(json_encode(Array("DataID"=> "{485663CC-3911-FAC7-9FCC-6E4D06438527}", 
						"Function" => "getGoogleMapsInstanceID")));
		$Result = $this->CheckGoogleMapsModuleID($GoogleMapsInstanceID);
		If ($Result == true) {
			$points = [
				['lat' => $Latitude, 'lng' => $Longitude]
				];

			// allgemeine Angaben zur Karte
			$map = [];

			// Mittelpunkt der Karte
			$map['center'] = $points[0];

			$map['zoom'] = 18;
			$map['size'] = '1000x1000';
			$map['scale'] = 2;
			$map['maptype'] = 'roadmap';

			$styles = [];

			$map['styles'] = $styles;

			$markers = [];

			$marker_points = [];
			$marker_points[0] = $points[0];

			$markers[] = [
			    'color'     => 'green',
			    'label'		   => 'P',
			    'points'    => $marker_points,
			];

			$marker_points = [];


			$markers[] = [
			    'color'     => '0x0000ff',
			    'size'      => 'tiny',
			    'points'    => $marker_points,
			];

			$map['markers'] = $markers;

			$url = GoogleMaps_GenerateStaticMap($GoogleMapsInstanceID, json_encode($map));

			$html = '<img width="1000", height="1000" src="' . $url . '" />';
			SetValueString($this->GetIDForIdent("GoogleMaps"), $html);
		}
		else {
			SetValueString($this->GetIDForIdent("GoogleMaps"), "Karte konnte nicht erstellt werden (Keine gültige GoogleMaps-Instanz benannt).");
		}
	}
	
	private function CheckGoogleMapsModuleID(int $InstanceID)
	{
		$Result = false;
		If ($InstanceID >= 10000) {
			$ModuleID = (IPS_GetInstance($InstanceID)['ModuleInfo']['ModuleID']); 
			If ($ModuleID == "{2C639155-4F49-4B9C-BBA5-1C7E62F1CF54}") {
				$Result = true;
			}
			else {
				$this->SendDebug("CheckGoogleMapsModuleID", "Fehlerhafte GoogleMaps-Schnittstelle! (keine korrekte GoogleMaps-Instanz)", 0);
			}
		}
		else {
			$this->SendDebug("CheckGoogleMapsModuleID", "Fehlerhafte GoogleMaps-Schnittstelle! (keine korrekte GoogleMaps-Instanz)", 0);
		}
	return $Result;
	}    
	    
	    
	private function RegisterProfileBoolean($Name, $Icon)
	{
	        if (!IPS_VariableProfileExists($Name))
	        {
	            IPS_CreateVariableProfile($Name, 0);
	        }
	        else
	        {
	            $profile = IPS_GetVariableProfile($Name);
	            if ($profile['ProfileType'] != 0)
	                throw new Exception("Variable profile type does not match for profile " . $Name);
	        }
	        IPS_SetVariableProfileIcon($Name, $Icon);
	}        
	    
	private function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize)
	{
	        if (!IPS_VariableProfileExists($Name))
	        {
	            IPS_CreateVariableProfile($Name, 1);
	        }
	        else
	        {
	            $profile = IPS_GetVariableProfile($Name);
	            if ($profile['ProfileType'] != 1)
	                throw new Exception("Variable profile type does not match for profile " . $Name);
	        }
	        IPS_SetVariableProfileIcon($Name, $Icon);
	        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
	        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);        
	}    
	    
	private function RegisterProfileFloat($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits)
	{
	        if (!IPS_VariableProfileExists($Name))
	        {
	            IPS_CreateVariableProfile($Name, 2);
	        }
	        else
	        {
	            $profile = IPS_GetVariableProfile($Name);
	            if ($profile['ProfileType'] != 2)
	                throw new Exception("Variable profile type does not match for profile " . $Name);
	        }
	        IPS_SetVariableProfileIcon($Name, $Icon);
	        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
	        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
	        IPS_SetVariableProfileDigits($Name, $Digits);
	}
}
?>
