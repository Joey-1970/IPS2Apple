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
		$this->RegisterPropertyString("MapType", "roadmap");
		$this->RegisterPropertyInteger("MapWidth", 640);
		$this->RegisterPropertyInteger("MapHeight", 640);
		$this->RegisterPropertyInteger("MapScale", 1);
		$this->RegisterPropertyInteger("MapZoom", 18);
		
		// Profil anlegen
		$this->RegisterProfileBoolean("JaNein.IPS2Apple", "Information");
		IPS_SetVariableProfileAssociation("JaNein.IPS2Apple", 0, "Nein", "", -1);
		IPS_SetVariableProfileAssociation("JaNein.IPS2Apple", 1, "Ja", "", -1);
		
		$this->RegisterProfileFloat("Meter.IPS2Apple", "Distance", "", " m", 0, 1000, 0.1, 1);
		
		$this->RegisterProfileFloat("KiloMeter.IPS2Apple", "Distance", "", " km", 0, 1000, 0.1, 1);
		
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
		$this->RegisterVariableFloat("Distance", "Distanz", "KiloMeter.IPS2Apple", $LocationPostion + 130); 
		
		$this->RegisterVariableBoolean("PlaySound", "Spiele Suchton", "~Switch", $LocationPostion + 140); 
		$this->EnableAction("PlaySound");
		
		$this->RegisterVariableString("Message", "Nachricht", "~TextBox", $LocationPostion + 150); 
		$this->EnableAction("Message");
		
		$this->RegisterVariableBoolean("SendMessage", "Sende Nachricht", "~Switch", $LocationPostion + 160); 
		$this->EnableAction("SendMessage");
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
		$arrayElements[] = array("type" => "Label", "label" => "Kartendarstellungsoptionen"); 
		$arrayOptions = array();
		$arrayOptions[] = array("label" => "Strassenkarte", "value" => "roadmap");
		$arrayOptions[] = array("label" => "Satellit", "value" => "satellite");
		$arrayOptions[] = array("label" => "Hybrid", "value" => "hybrid");
		$arrayOptions[] = array("label" => "Terrain", "value" => "terrain");
		$arrayElements[] = array("type" => "Select", "name" => "MapType", "caption" => "Kartentyp", "options" => $arrayOptions );
		$arrayElements[] = array("type" => "Label", "label" => "Der Maximalwert ist 640px"); 
		$arrayElements[] = array("type" => "IntervalBox", "name" => "MapWidth", "caption" => "Kartenbreite (px)");
		$arrayElements[] = array("type" => "IntervalBox", "name" => "MapHeight", "caption" => "Kartenhöhe (px)");
		$arrayOptions = array();
		$arrayOptions[] = array("label" => "1-fach", "value" => 1);
		$arrayOptions[] = array("label" => "2-fach", "value" => 2);
		$arrayElements[] = array("type" => "Select", "name" => "MapScale", "caption" => "Kartenskalierung", "options" => $arrayOptions );
		$arrayOptions = array();
		for ($i = 1; $i <= 20; $i++) {
			$arrayOptions[] = array("label" => $i, "value" => $i);
		}
		$arrayElements[] = array("type" => "Label", "label" => "1 (Welt) - 20 (Gebäude)"); 
		$arrayElements[] = array("type" => "Select", "name" => "MapZoom", "caption" => "Zoom", "options" => $arrayOptions );

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
		$TopicFilter = '.*"DeviceID":"' . preg_quote(substr(json_encode($this->ReadPropertyString("DeviceID")), 1, -1)) . '.*';
		//$Filter = '(.*"DeviceID":"'.$this->ReadPropertyString("DeviceID").'".*)';
		$this->SetReceiveDataFilter($TopicFilter);
		
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
	
	public function RequestAction($Ident, $Value) 
	{
  		switch($Ident) {
	        	case "PlaySound":
				$this->SetValue("PlaySound", true);
		    		$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{485663CC-3911-FAC7-9FCC-6E4D06438527}", 
						"Function" => "PlaySound", "DeviceID" => $this->ReadPropertyString("DeviceID") )));
				$this->SetValue("PlaySound", false);
	            		break;
			case "SendMessage":
				$this->SetValue("SendMessage", true);
		    		$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{485663CC-3911-FAC7-9FCC-6E4D06438527}", 
						"Function" => "SendMessage", "DeviceID" => $this->ReadPropertyString("DeviceID"), "Message" => $this->GetValue("Message") )));
				$this->SetValue("SendMessage", false);
	            		break;
			case "Message":
				// Keine Aktion notwendig
	            		break;
	        default:
	            	throw new Exception("Invalid Ident");
	    	}
	}    
	    
	// Beginn der Funktionen
	private function ShowData(string $DeviceData)
	{
		$DeviceDataArray = unserialize($DeviceData);
		$this->SetStatus(102);
		$this->SetValue("LastUpdate", time());
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
			$this->GoogleMaps($DeviceDataArray->location->latitude, $DeviceDataArray->location->longitude, $DeviceDataArray->name);
			$this->GPS_Distanz($DeviceDataArray->location->latitude, $DeviceDataArray->location->longitude, $DeviceDataArray->location->altitude);
        	} 
		else {
			SetValueString($this->GetIDForIdent("GoogleMaps"), "Karte konnte nicht erstellt werden (Keine aktuellen Daten).");
		}
		$this->SendDebug("ShowData", serialize($DeviceDataArray), 0);
		//$this->SendDebug("ShowData", $DeviceDataArray->location->longitude, 0);
	}
	
	private function GoogleMaps(float $Latitude, float $Longitude, string $DeviceName)
	{
		$GoogleMapsInstanceID = $this->SendDataToParent(json_encode(Array("DataID"=> "{485663CC-3911-FAC7-9FCC-6E4D06438527}", 
						"Function" => "getGoogleMapsInstanceID")));
		$Result = $this->CheckGoogleMapsModuleID($GoogleMapsInstanceID);
		If ($Result == true) {
			$MapWidth = min(640, max(0, $this->ReadPropertyInteger("MapWidth")));
			$MapHeight = min(640, max(0, $this->ReadPropertyInteger("MapHeight")));
			$MapScale = min(2, max(1, $this->ReadPropertyInteger("MapScale")));
			$MapType = $this->ReadPropertyString("MapType");
			$MapZoom = min(20, max(1, $this->ReadPropertyInteger("MapZoom")));
			
			$points = [
				['lat' => $Latitude, 'lng' => $Longitude]
				];

			// allgemeine Angaben zur Karte
			$map = [];

			// Mittelpunkt der Karte
			$map['center'] = $points[0];
			$map['zoom'] = $MapZoom;
			$MapSize = $MapWidth."x".$MapHeight;
			$map['size'] = $MapSize;
			$map['scale'] = $MapScale;
			$map['maptype'] = $MapType;

			$styles = [];
			$map['styles'] = $styles;

			$markers = [];

			$marker_points = [];
			$marker_points[0] = $points[0];

			$markers[] = [
			    'color'     => 'green',
			    'label'	=> strtoupper(substr($DeviceName, 0, 1)),
			    'points'    => $marker_points,
			];
			$map['markers'] = $markers;

			$url = GoogleMaps_GenerateStaticMap($GoogleMapsInstanceID, json_encode($map));
			//$url = GoogleMaps_GenerateEmbededMap($GoogleMapsInstanceID, json_encode($map));
			
			$html = '<img width="'.($MapWidth * $MapScale).'", height="'.($MapHeight * $MapScale).'" src="' . $url . '" />';
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
	  
	// Berechnet aus zwei GPS-Koordinaten die Entfernung
	private function GPS_Distanz(float $Latitude, float $Longitude, float $Altitude)
	{
		$locationObject = unserialize($this->SendDataToParent(json_encode(Array("DataID"=> "{485663CC-3911-FAC7-9FCC-6E4D06438527}", 
					 "Function" => "getLocation"))));
		$HomeLatitude = $locationObject['latitude'];
		$HomeLongitude = $locationObject['longitude']; 
		$this->SendDebug("GPS_Distanz", $HomeLatitude." - ".$HomeLongitude." - ".$Latitude." - ".$Longitude, 0);
		
		$HomeHeightOverNN = 0; //$this->ReadPropertyInteger("HeightOverNN") / 1000; // Umrechnung in km
		$Altitude = $Altitude / 1000; // Umrechnung von ft in km
		
		$km = 0;
		$pi80 = M_PI / 180;
		$Latitude *= $pi80;
		$Longitude *= $pi80;
		$HomeLatitude *= $pi80;
		$HomeLongitude *= $pi80;

		$r = 6372.797; // mean radius of Earth in km
		$dlat = $HomeLatitude - $Latitude;
		$dlng = $HomeLongitude - $Longitude;
		$a = sin($dlat / 2) * sin($dlat / 2) + cos($Latitude) * cos($HomeLatitude) * sin($dlng / 2) * sin($dlng / 2);
		$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
		$Distence2d = $r * $c;
		
		// Um Höhe korrigieren
		$dheight = $Altitude - $HomeHeightOverNN;
		$km = sqrt(pow($Distence2d, 2) + pow($dheight, 2));
		$km = round($km, 1);
		$this->SetValue("Distance", $km);
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
