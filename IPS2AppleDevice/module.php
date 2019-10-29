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
		
		// Statusvariablen anlegen
		$this->RegisterVariableInteger("LastUpdate", "Letztes Update", "~UnixTimestamp", 10);
		
		$this->RegisterVariableBoolean("isOld", "ist alt", "", 20);
		$this->RegisterVariableFloat("Longitude", "Longitude", "", 30);
		$this->RegisterVariableFloat("Latitude", "Latitude", "", 40);
		$this->RegisterVariableFloat("Altitude", "Altitude", "", 50);
		$this->RegisterVariableBoolean("isInaccurate", "ist Inakkurat", "", 60);
		$this->RegisterVariableString("positionType", "Position Typ", "", 70);
           	$this->RegisterVariableInteger("floorLevel", "Stockwerk", "", 80);
            	$this->RegisterVariableFloat("horizontalAccuracy", "Horizontale Akkuratheit", "", 90);
		$this->RegisterVariableFloat("verticalAccuracy", "Vertikale Akkuratheit", "", 100);
           	$this->RegisterVariableInteger("timeStamp", "Zeitstempel", "~UnixTimestamp", 110);
           	$this->RegisterVariableBoolean("locationFinished", "Messung abgeschlossen", "", 120);
            	$this->RegisterVariableString("locationType", "Lokation Typ", "", 130);
            
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
		
		//ReceiveData-Filter setzen
		$DeviceID = $this->ReadPropertyString("DeviceID");
		$Filter = '(.*"DeviceID":'.$DeviceID.'.*)';
		//$this->SetReceiveDataFilter($Filter);
		
		If ($this->HasActiveParent() == true) {
			$this->SetStatus(102);	
		}
		else {
			$this->SetStatus(104);
		}
		
	}
	
	
	 
	public function ReceiveData($JSONString) 
	{
	 	// Empfangene Daten vom Gateway/Splitter
	    	$data = json_decode($JSONString);
	 	switch ($data->Function) {
			case "set_State":
			    	$this->SendDebug("RequestAction", "Ankommende ID:".$data->DeviceID, 0);
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
		If (property_exists($DeviceDataArray, "location->longitude")) {
			SetValueFloat($this->GetIDForIdent("Longitude"), $DeviceDataArray->location->longitude);
        	}  
		If (property_exists($DeviceDataArray, "location->latitude")) {
			SetValueFloat($this->GetIDForIdent("Latitude"), $DeviceDataArray->location->latitude);
        	} 
		$this->SendDebug("ShowData", serialize($DeviceDataArray), 0);
		//$this->SendDebug("ShowData", $DeviceDataArray->location->longitude, 0);
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
