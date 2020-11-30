<?
    // Klassendefinition
    class IPS2AppleSplitter extends IPSModule 
    {
	   
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
		$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyString("iCloudUser", "iCloud-Benutzer");
		$this->RegisterPropertyString("iCloudPassword", "iCloud-Passwort");
		$this->RegisterPropertyInteger("DataUpdate", 5);
		$this->RegisterPropertyInteger("GoogleMapsInstanceID", 0);
		$this->RegisterPropertyString("Location", '{"latitude":0,"longitude":0}');
		$this->RegisterTimer("DataUpdate", 0, 'IPS2AppleSplitter_GetData($_IPS["TARGET"]);');
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
		$arrayElements[] = array("type" => "Label", "label" => "iCloud-Zugriffsdaten");
		$arrayElements[] = array("type" => "ValidationTextBox", "name" => "iCloudUser", "caption" => "User");
		$arrayElements[] = array("type" => "PasswordTextBox", "name" => "iCloudPassword", "caption" => "Password");
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "DataUpdate", "caption" => "Daten-Update (min)");
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "label" => "GoogleMaps Instanz ID (GoogleMaps-Modul ist im Modul-Store erhältlich)"); 
		$arrayElements[] = array("type" => "SelectInstance", "name" => "GoogleMapsInstanceID", "caption" => "GoogleMaps-Instanz");
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "SelectLocation", "name" => "Location", "caption" => "Region");
		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
 	}       
	   
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
            	// Diese Zeile nicht löschen
            	parent::ApplyChanges();
		
		If ($this->ReadPropertyBoolean("Open") == true) {
			$GoogleMapsInstanceID = $this->ReadPropertyInteger("GoogleMapsInstanceID");
			$this->CheckGoogleMapsModuleID($GoogleMapsInstanceID);
			$this->SetStatus(102);
			$this->GetData();
			$this->SetTimerInterval("DataUpdate", $this->ReadPropertyInteger("DataUpdate") * 60 * 1000);
		}
		else {
			$this->SetStatus(104);
			$this->SetTimerInterval("DataUpdate", 0);
		}	
	}
	
	public function ForwardData($JSONString) 
	 {
	 	// Empfangene Daten von der Device Instanz
	    	$data = json_decode($JSONString);
	    	$Result = false;
	 	switch ($data->Function) {
			case "getData":
				$this->GetData();
				break;
			case "getConfiguratorData":
				$Result = $this->GetConfiguratorData();
				break;
			case "getGoogleMapsInstanceID":
				$Result = $this->ReadPropertyInteger("GoogleMapsInstanceID");
				break;
			case "getLocation":
				$locationObject = json_decode($this->ReadPropertyString('Location'), true);		
				$Result = serialize($locationObject);				
				break;
			case "PlaySound":
				$Result = $this->PlaySound($data->DeviceID);
				break;
			case "SendMessage":
				$Result = $this->PlaySound($data->DeviceID, $data->Message);
				break;
		}
	return $Result;
	}
	    
	// Beginn der Funktionen
	public function GetData()
	{
		If ($this->ReadPropertyBoolean("Open") == true) {
			set_include_path(__DIR__.'/../libs');
			require_once (__DIR__ .'/../libs/FindMyiPhone.php');

			$iCloudUser = $this->ReadPropertyString("iCloudUser");;
			$iCloudPassword = $this->ReadPropertyString("iCloudPassword");

			$FindMyiPhone = new FindMyiPhone($iCloudUser, $iCloudPassword); 
			$AppleDevices = array();

			$AppleDevices = $FindMyiPhone->devices; 

			$this->SendDebug("GetData", serialize($AppleDevices), 0);
			
			foreach ($AppleDevices as $DeviceData) {
    				$DeviceID = $DeviceData->id;
				$this->SendDebug("GetData", $DeviceID, 0);
				$this->SendDataToChildren(json_encode(Array("DataID" => "{BEF67A8E-7EBF-7A20-588E-7B1F0CC4DD1A}", 
					"Function"=>"set_State", "DeviceID" => $DeviceID, "DeviceDataArray"=> serialize($DeviceData))));

			}
		}
	}
	
	public function GetConfiguratorData()
	{
		$DeviceArray = array();
		If ($this->ReadPropertyBoolean("Open") == true) {
			set_include_path(__DIR__.'/../libs');
			require_once (__DIR__ .'/../libs/FindMyiPhone.php');
			$iCloudUser = $this->ReadPropertyString("iCloudUser");;
			$iCloudPassword = $this->ReadPropertyString("iCloudPassword");
			$FindMyiPhone = new FindMyiPhone($iCloudUser, $iCloudPassword); 
			$AppleDevices = array();
			$AppleDevices = $FindMyiPhone->devices; 
			$this->SendDebug("GetConfiguratorData", serialize($AppleDevices), 0);
			
			$i = 0;
			foreach ($AppleDevices as $DeviceData) {
    				$DeviceArray[$i]["DeviceID"] = $DeviceData->id;
				$DeviceArray[$i]["DeviceModel"] = $DeviceData->modelDisplayName;
				$DeviceArray[$i]["DeviceName"] = $DeviceData->name;
				$DeviceArray[$i]["InstanceID"] = 0;
				$i = $i + 1;
			}
		}
	return serialize($DeviceArray);
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
				Echo "Fehlerhafte GoogleMaps-Schnittstelle! \n(keine korrekte GoogleMaps-Instanz)\n";
			}
		}
		else {
			//Echo "Fehlende GoogleMaps-Schnittstelle! \n";
		}
	return $Result;
	}
	
	private function PlaySound($DeviceID)
	{
		$Result = false;
		If ($this->ReadPropertyBoolean("Open") == true) {
			set_include_path(__DIR__.'/../libs');
			require_once (__DIR__ .'/../libs/FindMyiPhone.php');

			$iCloudUser = $this->ReadPropertyString("iCloudUser");;
			$iCloudPassword = $this->ReadPropertyString("iCloudPassword");

			$FindMyiPhone = new FindMyiPhone($iCloudUser, $iCloudPassword); 
			
			If ($FindMyiPhone->play_sound($DeviceID, "IP-Symcon")->statusCode == 200) {
				$Result = true;
			}
		}
	return $Result;
	}
	    
	private function SendMessage($DeviceID, $Message)
	{
		$Result = false;
		If ($this->ReadPropertyBoolean("Open") == true) {
			set_include_path(__DIR__.'/../libs');
			require_once (__DIR__ .'/../libs/FindMyiPhone.php');

			$iCloudUser = $this->ReadPropertyString("iCloudUser");;
			$iCloudPassword = $this->ReadPropertyString("iCloudPassword");

			$FindMyiPhone = new FindMyiPhone($iCloudUser, $iCloudPassword); 

			If ($FindMyiPhone->send_message($DeviceID, $Message, false, 'IP-Symcon')->statusCode == 200) {
				$Result = true;
			}
		}
	return $Result;
	}
	    
	/*
	private function FileTest()
	{
		// Schriftartpfad
		$Result = false;
		set_include_path(__DIR__.'/../libs');
		$FileName = (__DIR__ .'/../libs/FindMyiPhone.php');
		if (file_exists($FileName)) {
			$this->SendDebug("FileTest", "Datei ".$FileName." gefunden!", 0);
			$Result = true;
		}
		else {
			$this->SendDebug("FileTest", "Datei ".$FileName." nicht gefunden!", 0);
			$Result = false;
		}
	return $Result;
	}

	*/
}
?>
