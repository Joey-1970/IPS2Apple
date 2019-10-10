<?
    // Klassendefinition
    class IPS2TankerkoenigStation extends IPSModule 
    {
	public function Destroy() 
	{
		//Never delete this line!
		parent::Destroy();
		$this->SetTimerInterval("Timer_1", 0);
	}
	    
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
		$this->ConnectParent("{66FD608F-6C67-6011-25E3-B9ED4C3E1590}");
		$this->RegisterPropertyString("StationID", "");
		$this->RegisterPropertyInteger("Timer_1", 10);
		$this->RegisterTimer("Timer_1", 0, 'I2TStation_GetDataUpdate($_IPS["TARGET"]);');
		$this->RegisterPropertyBoolean("Diesel", true);
		$this->RegisterPropertyBoolean("E5", true);
		$this->RegisterPropertyBoolean("E10", true);
		
		// Profil anlegen
		$this->RegisterProfileFloat("IPS2Tankerkoenig.Euro", "Euro", "", " €", 0, 1000, 0.001, 3);
		
		$this->RegisterProfileInteger("IPS2Tankerkoenig.State", "Information", "", "", 0, 2, 1);
		IPS_SetVariableProfileAssociation("IPS2Tankerkoenig.State", 0, "Unbekannt", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Tankerkoenig.State", 1, "Geöffnet", "LockOpen", 0x00FF00);
		IPS_SetVariableProfileAssociation("IPS2Tankerkoenig.State", 2, "Geschlossen", "LockClosed", 0xFF0000);
		
		// Status-Variablen anlegen
		$this->RegisterVariableString("PetrolStation", "Tankstelle", "~HTMLBox", 10);
		
		$this->RegisterVariableInteger("LastUpdate", "Letztes Update", "~UnixTimestamp", 20);
			
		$this->RegisterVariableFloat("Diesel", "Diesel", "IPS2Tankerkoenig.Euro", 30);
		$this->RegisterVariableFloat("E5", "Super E5", "IPS2Tankerkoenig.Euro", 40);
		$this->RegisterVariableFloat("E10", "Super E10", "IPS2Tankerkoenig.Euro", 50);
		
		$this->RegisterVariableInteger("State", "Status", "IPS2Tankerkoenig.State", 60);
        }
 	
	public function GetConfigurationForm() 
	{ 
		$arrayStatus = array(); 
		$arrayStatus[] = array("code" => 101, "icon" => "inactive", "caption" => "Instanz wird erstellt"); 
		$arrayStatus[] = array("code" => 102, "icon" => "active", "caption" => "Instanz ist aktiv");
		$arrayStatus[] = array("code" => 104, "icon" => "inactive", "caption" => "Instanz ist inaktiv");
		$arrayStatus[] = array("code" => 202, "icon" => "error", "caption" => "Kommunikationfehler!");
				
		$arrayElements = array(); 
		$arrayElements[] = array("type" => "ValidationTextBox", "name" => "StationID", "caption" => "Tankstellen ID");
		$arrayElements[] = array("type" => "Label", "label" => "Aktualisierung (gemäß Tankerkönig.de Minimum 10 Minuten)");
		$arrayElements[] = array("type" => "IntervalBox", "name" => "Timer_1", "caption" => "min");
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "label" => "Anzuzeigende Sorten");
		$arrayElements[] = array("type" => "CheckBox", "name" => "Diesel", "caption" => "Diesel"); 
		$arrayElements[] = array("type" => "CheckBox", "name" => "E5", "caption" => "Super E5");
		$arrayElements[] = array("type" => "CheckBox", "name" => "E10", "caption" => "Super E10");
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Button", "caption" => "Tankerkönig-API", "onClick" => "echo 'https://creativecommons.tankerkoenig.de/';");
		$arrayActions = array();
		If ((strlen($this->ReadPropertyString("StationID")) > 0) AND ($this->HasActiveParent() == true)) {
			$this->GetDataUpdate();
			$arrayActions[] = array("type" => "Label", "label" => "Daten der Tankstelle bei Tankerkoenig.de korrigieren");
			$arrayActions[] = array("type" => "ValidationTextBox", "name" => "Brand", "caption" => "Marke", "value" => $this->GetBuffer("Brand"));
			$arrayActions[] = array("type" => "ValidationTextBox", "name" => "Name", "caption" => "Name", "value" => $this->GetBuffer("Name"));
			$arrayItems = array();
			$arrayItems[] = array("type" => "ValidationTextBox", "name" => "Street", "caption" => "Strasse", "value" => $this->GetBuffer("Street"));
			$arrayItems[] = array("type" => "ValidationTextBox", "name" => "HouseNumber", "caption" => "Hausnummer", "value" => $this->GetBuffer("HouseNumber"));
			$arrayActions[] = array("type" => "RowLayout", "items" => $arrayItems);
			$arrayItems = array();
			$arrayItems[] = array("type" => "ValidationTextBox", "name" => "PostCode", "caption" => "PLZ", "value" => $this->GetBuffer("PostCode"));
			$arrayItems[] = array("type" => "ValidationTextBox", "name" => "Place", "caption" => "Ort", "value" => $this->GetBuffer("Place"));
			$arrayActions[] = array("type" => "RowLayout", "items" => $arrayItems);
			$arrayActions[] = array("type" => "Button", "label" => "Korrektur auslösen", "onClick" => 'I2TStation_SetDataUpdate($id, $Brand, $Name, $Street, $HouseNumber, $PostCode, $Place);');
		}
		else {
			$arrayActions[] = array("type" => "Label", "label" => "Diese Funktionen stehen erst nach Eingabe und Übernahme der erforderlichen Daten zur Verfügung!");
		}
		
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements, "actions" => $arrayActions)); 		 
 	}       
	   
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
            	// Diese Zeile nicht löschen
            	parent::ApplyChanges();
		
		If (IPS_GetKernelRunlevel() == 10103) {	
			If ($this->HasActiveParent() == true) {
				$this->SetStatus(102);
				SetValueInteger($this->GetIDForIdent("State"), 1);
				$this->SetTimerInterval("Timer_1", $this->ReadPropertyInteger("Timer_1") * 1000 * 60);
				If (strlen($this->ReadPropertyString("StationID")) > 0) {
					$this->GetDataUpdate();
				}
				else {
					$this->SendDebug("GetDataUpdate", "Keine Tnkstellen ID verfügbar!", 0);
				}
			}
			else {
				$this->SetStatus(104);
			}
		}
	}
	    
	// Beginn der Funktionen
	public function GetDataUpdate()
	{
		$StationID = $this->ReadPropertyString("StationID");
		If (($this->isValidUuid($StationID)) AND ($this->HasActiveParent() == true)) {
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{6ADD0473-D761-A2BF-63BE-CFE279089F5A}", 
				"Function" => "GetDetailInformation", "InstanceID" => $this->InstanceID, "StationID" => $StationID)));
			If ($Result <> false) {
				$this->SetStatus(102);
				$this->SendDebug("GetDataUpdate", $Result, 0);
				$this->ShowResult($Result);
			}
			else {
				$this->SetStatus(202);
				$this->SendDebug("GetDataUpdate", "Fehler bei der Datenermittlung!", 0);
			}
		}
		else {
			$this->SendDebug("GetStationDetails", "Stations ID fehlerhaft!", 0);
		}
	}
	
	private function ShowResult(string $Text)
	{
		$ResultArray = array();
		$ResultArray = json_decode($Text);
		$ColorCode = "#00FF00";
		// Fehlerbehandlung
		If (boolval($ResultArray->ok) == false) {
			$this->SendDebug("ShowResult", "Fehler bei der Datenermittlung: ".utf8_encode($ResultArray->message), 0);
			return;
		} 
		// Daten für die Korrekturen
		$this->SetBuffer("Brand", "".$ResultArray->station->brand);
		$this->SetBuffer("Name", "".$ResultArray->station->name);
		$this->SetBuffer("Street", "".$ResultArray->station->street);
		$this->SetBuffer("HouseNumber", "".$ResultArray->station->houseNumber);
		$this->SetBuffer("PostCode", "".$ResultArray->station->postCode);
		$this->SetBuffer("Place", "".$ResultArray->station->place);
		
		// Tabelle aufbauen
		$table = '<style type="text/css">';
		$table .= '<link rel="stylesheet" href="./.../webfront.css">';
		$table .= "</style>";
		$table .= '<table class="tg">';
		$table .= '<tr>';
		$table .= '<td class="tg-611x">'.ucwords(strtolower($ResultArray->station->brand)).'</td>';
		$table .= '</tr>';
		$table .= '<tr>';
		$table .= '<td class="tg-611x">'.ucwords(strtolower($ResultArray->station->name)).'</td>';
		$table .= '</tr>';
		$table .= '<tr>';
		$table .= '<td class="tg-611x">'.ucwords(strtolower($ResultArray->station->street))." ".$ResultArray->station->houseNumber.'</td>';
		$table .= '</tr>';
		$table .= '<tr>';
		$table .= '<td class="tg-611x">'.$ResultArray->station->postCode." ".ucwords(strtolower($ResultArray->station->place)).'</td>';
		$table .= '</tr>';
		If (boolval($ResultArray->station->wholeDay) == true) {
			$table .= '<tr>';
			$table .= '<td class="tg-611x">'."Ganztägig geöffnet".'</td>';
			$table .= '</tr>';
		}
		else {
			$table .= '<tr>';
			$table .= '<td class="tg-611x">'."Öffnungszeiten:".'</td>';
			$table .= '</tr>';
			foreach($ResultArray->station->openingTimes as $Open) {
				$table .= '<tr>';
				$table .= '<td class="tg-611x">'.$Open->text.'</td>';
				$table .= '<td class="tg-611x">'.$Open->start." Uhr bis ".'</td>';
				$table .= '<td class="tg-611x">'.$Open->end." Uhr".'</td>';
				$table .= '</tr>';
			}
			If (boolval($ResultArray->station->isOpen) == true) {
				$table .= '<tr>';
				$table .= '<td class="tg-611x">'."Aktuell geöffnet".'</td>';
				$table .= '</tr>';
				SetValueInteger($this->GetIDForIdent("State"), 1);
			}
			else {
				$table .= '<tr>';
				$table .= '<td class="tg-611x">'."Aktuell geschlossen".'</td>';
				$table .= '</tr>';
				SetValueInteger($this->GetIDForIdent("State"), 2);
			}
			foreach($ResultArray->station->overrides as $Closed) {
				$table .= '<tr>';
				$table .= '<td class="tg-611x">'.$Closed.'</td>';
				$table .= '</tr>';
			}
		}
		$table .= '</table>';
		$Lat = floatval($ResultArray->station->lat);
		$Long = floatval($ResultArray->station->lng);
		
		If ($table <> GetValueString($this->GetIDForIdent("PetrolStation"))) {
			SetValueString($this->GetIDForIdent("PetrolStation"), $table);
		}
		$Diesel = floatval($ResultArray->station->diesel);
		$E5 = floatval($ResultArray->station->e5);
		$E10 = floatval($ResultArray->station->e10);
		//If ($Diesel <> GetValueFloat($this->GetIDForIdent("Diesel"))) {
			SetValueFloat($this->GetIDForIdent("Diesel"), $Diesel);
		//}
		//If ($E5 <> GetValueFloat($this->GetIDForIdent("E5"))) {
			SetValueFloat($this->GetIDForIdent("E5"), $E5);
		//}
		//If ($E10 <> GetValueFloat($this->GetIDForIdent("E10"))) {
			SetValueFloat($this->GetIDForIdent("E10"), $E10);
		//}
		SetValueInteger($this->GetIDForIdent("LastUpdate"), time() );
	}
	
	public function SetDataUpdate(string $Brand, string $Name, string $Street, string $HouseNumber, string $PostCode, string $Place)
	{
		$this->SendDebug("SetDataUpdate", $Brand, 0);
		$StationID = $this->ReadPropertyString("StationID");
		If (($this->isValidUuid($StationID)) AND ($this->HasActiveParent() == true)) {
			If ($Brand <>  $this->GetBuffer("Brand")) {
				$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{6ADD0473-D761-A2BF-63BE-CFE279089F5A}", 
					"Function" => "DataCorrection", "InstanceID" => $this->InstanceID, "StationID" => $StationID, "Field" => "wrongPetrolStationBrand", "Value" => $Brand)));
			}
			If ($Name <> $this->GetBuffer("Name")) {
				$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{6ADD0473-D761-A2BF-63BE-CFE279089F5A}", 
					"Function" => "DataCorrection", "InstanceID" => $this->InstanceID, "StationID" => $StationID, "Field" => "wrongPetrolStationName", "Value" => $Name)));
			}
			If ($Street <> $this->GetBuffer("Street")) {
				$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{6ADD0473-D761-A2BF-63BE-CFE279089F5A}", 
					"Function" => "DataCorrection", "InstanceID" => $this->InstanceID, "StationID" => $StationID, "Field" => "wrongPetrolStationStreet", "Value" => $Street)));
			}
			If ($HouseNumber <> $this->GetBuffer("HouseNumber")) {
				$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{6ADD0473-D761-A2BF-63BE-CFE279089F5A}", 
					"Function" => "DataCorrection", "InstanceID" => $this->InstanceID, "StationID" => $StationID, "Field" => "wrongPetrolStationHouseNumber", "Value" => $HouseNumber)));
			}
			If ($PostCode <> $this->GetBuffer("PostCode")) {
				$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{6ADD0473-D761-A2BF-63BE-CFE279089F5A}", 
					"Function" => "DataCorrection", "InstanceID" => $this->InstanceID, "StationID" => $StationID, "Field" => "wrongPetrolStationPostcode", "Value" => $PostCode)));
			}
			If ($Place <> $this->GetBuffer("Place")) {
				$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{6ADD0473-D761-A2BF-63BE-CFE279089F5A}", 
					"Function" => "DataCorrection", "InstanceID" => $this->InstanceID, "StationID" => $StationID, "Field" => "wrongPetrolStationPlace", "Value" => $Place)));
			}
		}
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
