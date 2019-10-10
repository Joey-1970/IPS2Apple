<?
    // Klassendefinition
    class IPS2TankerkoenigIO extends IPSModule 
    {
	
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
		$this->RegisterPropertyBoolean("Open", false);
 	    	$this->RegisterPropertyString("ApiKey", "");
        }
 	
	public function GetConfigurationForm() 
	{ 
		$arrayStatus = array(); 
		$arrayStatus[] = array("code" => 101, "icon" => "inactive", "caption" => "Instanz wird erstellt"); 
		$arrayStatus[] = array("code" => 102, "icon" => "active", "caption" => "Instanz ist aktiv");
		$arrayStatus[] = array("code" => 104, "icon" => "inactive", "caption" => "Instanz ist inaktiv");
		$arrayStatus[] = array("code" => 202, "icon" => "error", "caption" => "Kommunikationfehler!");
				
		$arrayElements = array(); 
		$arrayElements[] = array("name" => "Open", "type" => "CheckBox",  "caption" => "Aktiv");
		$arrayElements[] = array("type" => "Label", "label" => "Der benötigte API-Key ist auf der unten verlinkten Website von tankerkoenig.de erhältlich");
		$arrayElements[] = array("type" => "ValidationTextBox", "name" => "ApiKey", "caption" => "Tankerkönig API-Key");
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Button", "caption" => "Tankerkönig-API", "onClick" => "echo 'https://creativecommons.tankerkoenig.de/';");
		
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
 	}       
	   
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
            	// Diese Zeile nicht löschen
            	parent::ApplyChanges();
		If (IPS_GetKernelRunlevel() == 10103) {	
		
			If ($this->ReadPropertyBoolean("Open") == true) {
				$ApiKey = $this->ReadPropertyString("ApiKey");
				If ($this->isValidUuid($ApiKey)) {
					$this->SetStatus(102);
				}
				else {
					$this->SetStatus(104);
				}
			}
			else {
				$this->SetStatus(104);
			}
		}
	}
	
	public function ForwardData($JSONString) 
	 {
	 	// Empfangene Daten von der Device Instanz
	    	$data = json_decode($JSONString);
	    	$Result = -999;
	 	switch ($data->Function) {
			case "GetAreaInformation":
				$ApiKey = $this->ReadPropertyString("ApiKey");
				$Lat = floatval($data->Lat);
				$Long = floatval($data->Long);
				$Radius = floatval($data->Radius);
				$this->SendDebug("GetAreaInformation", $Lat.", ".$Long.", ".$Radius, 0);
	 			$Result = file_get_contents ("https://creativecommons.tankerkoenig.de/json/list.php?lat=".$Lat."&lng=".$Long."&rad=".$Radius."&sort=dist&type=all&apikey=".$ApiKey);
				If ($Result == false) {
					$this->SetStatus(202);
				}
				else {
					$this->SetStatus(102);
				}
				break;
			case "GetDetailInformation":
				$ApiKey = $this->ReadPropertyString("ApiKey");
				$StationID = $data->StationID;
				$this->SendDebug("GetDetailInformation", $StationID, 0);
				$Result = file_get_contents ("https://creativecommons.tankerkoenig.de/json/detail.php?id=".$StationID."&apikey=".$ApiKey);
				If ($Result == false) {
					$this->SetStatus(202);
				}
				else {
					$this->SetStatus(102);
				}
				break;
			case "DataCorrection":
				$StationID = $data->StationID;
				$this->SendDebug("DataCorrection", $StationID, 0);
				$complaintType = $data->Field;
				$Correction = $data->Value;
				$Result = $this->sendComplaint($StationID, $complaintType, $Correction);
				If ($Result == false) {
					$this->SetStatus(202);
				}
				else {
					$this->SetStatus(102);
				}
				break;
		}
	return $Result;
	}
	    
	// Beginn der Funktionen
	private function sendComplaint($StationID, $complaintType, $Correction)
	{
    		$this->SendDebug("sendComplaint", $StationID.", ".$complaintType.", ".$Correction, 0);
		$ApiKey = $this->ReadPropertyString("ApiKey");
		$Success = false;
    		$complaintTypeArray = array("wrongPetrolStationName", "wrongStatusOpen", "wrongStatusClosed", "wrongPriceE5", "wrongPriceE10", 
    			"wrongPriceDiesel", "wrongPetrolStationBrand", "wrongPetrolStationStreet", "wrongPetrolStationHouseNumber", 
    			"wrongPetrolStationPostcode", "wrongPetrolStationPlace", "wrongPetrolStationLocation");
   
    		if (in_array($complaintType, $complaintTypeArray)) {
        		$data = array("apikey" => $ApiKey, "id" => $StationID, "type" => $complaintType);
        		if ($Correction) {
            			$data["correction"] = $Correction;
        		}
        		$URL = "https://creativecommons.tankerkoenig.de/json/complaint.php";
        
        		$Options = array(
            			'http' => array(
            			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            			'method'  => 'POST',
            			'content' => http_build_query($data)));
        		$Context  = stream_context_create($Options);
        		$Result = file_get_contents($URL, false, $Context);
        		$ResultArray = array();
        		$ResultArray = json_decode($Result);
        		// Ergebnis auswerten
			If ($ResultArray->ok == true) {
            			$this->SendDebug("sendComplaint", "Korrektur erfolgreich durchgeführt", 0);
            			$Success = true;
        		}
        		else {
            			$this->SendDebug("sendComplaint", "Korrektur nicht erfolgreich! Fehler: ".$ResultArray->message, 0);
            			$Success = false;
        		}
    		}
    		else {
        		$this->SendDebug("sendComplaint", "Unbekannter Befehl: ".$complaintType, 0);
	       		$Success = false;
    		}
	return $Success;  
	}
	
	private function isValidUuid(string $UUID) 
	{
    		if (preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/', strtoupper($UUID))) {
        		//$this->SendDebug("isValidUuid", "UUID ist gültig", 0);
			return true;
    		}
		else {
			$this->SendDebug("isValidUuid", "UUID ist ungültig!", 0);
			return false;
    		}
	}
}
?>
