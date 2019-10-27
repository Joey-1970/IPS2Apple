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
		
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
 	}       
	   
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
            	// Diese Zeile nicht löschen
            	parent::ApplyChanges();
		If (IPS_GetKernelRunlevel() == 10103) {	
		
			If ($this->ReadPropertyBoolean("Open") == true) {
				$this->SetStatus(102);
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
				
				break;
			case "GetDetailInformation":
				
				break;
			case "DataCorrection":
				
				break;
		}
	return $Result;
	}
	    
	// Beginn der Funktionen
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
	    
	private function SendMessage()
	{
		set_include_path(__DIR__.'/../libs');
		require_once (__DIR__ .'/../libs/FindMyiPhone.php');
		
		$FindMyiPhone = new FindMyiPhone('BENUTZERNAME', 'PASSWORT');  // iCloud Benutzer/Passwort eingeben
		//$device_id = $FindMyiPhone->devices[1]->id;
		$text = 'Ich bin eine Nachricht.';
		echo 'Sende Nachricht... '."\n";
		echo ($FindMyiPhone->send_message($device_id, $text, false, 'IP-Symcon')->statusCode == 200) ? '...gesendet!' : '...Fehler!';
		echo PHP_EOL;
	}
}
?>
