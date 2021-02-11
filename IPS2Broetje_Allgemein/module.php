<?
    // Klassendefinition
    class IPS2Broetje_Allgemein extends IPSModule 
    { 
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
		$this->RegisterMessage(0, IPS_KERNELSTARTED);
		
		$this->ConnectParent("{A5F663AB-C400-4FE5-B207-4D67CC030564}");
	
            	$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyInteger("Timer_1", 60);
		$this->RegisterTimer("Timer_1", 0, 'IPS2BroetjeAllgemein_GetState($_IPS["TARGET"]);');
		
		// Profile anlegen
		$this->RegisterProfileInteger("IPS2Broetje.BurnerOutput", "Information", "", "", 0, 3, 0);
		IPS_SetVariableProfileAssociation("IPS2Broetje.BurnerOutput", 0, "Unbekannt", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.BurnerOutput", 1, "Teillast", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.BurnerOutput", 2, "Volllast", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.BurnerOutput", 3, "Maximale Heizleistung", "Information", -1);
		
		$this->RegisterProfileInteger("IPS2Broetje.Status", "Information", "", "", 0, 3, 0);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 0, "OK", "Information", 0x00FF00);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 1, "Inaktiv", "Alert", 0xFF0000);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 2, "Kurzschluß", "Alert", 0xFF0000);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 64, "Fehlerhaft", "Alert", 0xFF0000);
		
		$this->RegisterProfileFloat("IPS2Broetje.WaterPressure", "Information", "", " bar", 0, 6, 0.1, 1);
		
		
		// Status-Variablen anlegen
		$this->RegisterVariableInteger("LastUpdate", "Letztes Update", "~UnixTimestamp", 5);
		
		$this->RegisterVariableFloat("AussenTemperatur", "Aussentemperatur", "~Temperature", 10);
		
		$this->RegisterVariableInteger("StatusCommand_1", "Status Aussentemperatursensor", "IPS2Broetje.Status", 20);
		
		$this->RegisterVariableBoolean("ResetAlarmrelais", "Reset Alarmrelais", "~Switch", 30);
		$this->EnableAction("ResetAlarmrelais");
		
		$this->RegisterVariableBoolean("StatusAlarmrelais", "Status Alarmrelais", "~Switch", 40);
		
		$this->RegisterVariableInteger("StatusCommand_2", "Status Alarmrelais", "IPS2Broetje.Status", 50);
		
		$this->RegisterVariableBoolean("Schornsteinfegerfunktion", "Schornsteinfegerfunktion", "~Switch", 60);
		$this->EnableAction("Schornsteinfegerfunktion");
		
		$this->RegisterVariableInteger("Brennerleistung", "Brennerleistung", "IPS2Broetje.BurnerOutput", 70);
		$this->EnableAction("Brennerleistung");
		
		$this->RegisterVariableBoolean("Handbetrieb", "Handbetrieb", "~Switch", 80);
		$this->EnableAction("Handbetrieb");
		
		$this->RegisterVariableBoolean("Reglerstoppfunktion", "Reglerstoppfunktion", "~Switch", 90);
		$this->EnableAction("Reglerstoppfunktion");
		
		$this->RegisterVariableInteger("ReglerstoppSollwert", "Reglerstopp-Sollwert", "~Intensity.100", 100);
           	$this->EnableAction("ReglerstoppSollwert");	
		
		// Wasserdruck
		$this->RegisterVariableFloat("Wasserdruck", "Wasserdruck", "IPS2Broetje.WaterPressure", 110);
		$this->RegisterVariableInteger("StatusCommand_3", "Status Wasserdrucksensor", "IPS2Broetje.Status", 120);
		
		// Uhrzeit und Datum
		$this->RegisterVariableInteger("Systemzeit", "Systemzeit", "~UnixTimestamp", 130);
		
		
        }
       	
	public function GetConfigurationForm() { 
		$arrayStatus = array(); 
		$arrayStatus[] = array("code" => 101, "icon" => "inactive", "caption" => "Instanz wird erstellt"); 
		$arrayStatus[] = array("code" => 102, "icon" => "active", "caption" => "Instanz ist aktiv");
		$arrayStatus[] = array("code" => 104, "icon" => "inactive", "caption" => "Instanz ist inaktiv");
		$arrayStatus[] = array("code" => 200, "icon" => "error", "caption" => "Instanz ist fehlerhaft"); 
		$arrayStatus[] = array("code" => 202, "icon" => "error", "caption" => "Kommunikationfehler!");
		
		$arrayElements = array(); 
		$arrayElements[] = array("name" => "Open", "type" => "CheckBox",  "caption" => "Aktiv"); 
 		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");		
		$arrayElements[] = array("type" => "Label", "label" => "Auslesezyklus (Sekunden)");
		$arrayElements[] = array("type" => "IntervalBox", "name" => "Timer_1", "caption" => "s");
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		 
		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
 	} 
	
	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
                // Diese Zeile nicht löschen
                parent::ApplyChanges();
		
		If ($this->ReadPropertyBoolean("Open") == true) {
			If (IPS_GetKernelRunlevel() == KR_READY) {
				$this->GetState();
				$this->SetStatus(102);
				$this->SetTimerInterval("Timer_1", $this->ReadPropertyInteger("Timer_1") );
			}
		}
		else {
			$this->SetStatus(104);
			$this->SetTimerInterval("Timer_1", 0);
		}
		
	}
	
	public function RequestAction($Ident, $Value) 
	{
  		If ($this->ReadPropertyBoolean("Open") == true) {
			switch($Ident) {
				case "ResetAlarmrelais":
					$this->SetData(35862, $Value);
				    break;
			   	case "Schornsteinfegerfunktion":
					$this->SetData(35901, $Value);
				    break;
				case "Brennerleistung":
					$this->SetData(35903, $Value);
				    break;
				case "Handbetrieb":
					$this->SetData(35904, $Value);
				    break;
				case "Reglerstoppfunktion":
					$this->SetData(35905, $Value);
				    break;
				case "ReglerstoppSollwert":
					$this->SetData(35906, $Value);
				    break;
			default:
			    throw new Exception("Invalid Ident");
			}
		}
	}
	    
	public function ReceiveData($JSONString) 
	{
	    	// Empfangene Daten vom Gateway/Splitter
	    	$data = json_decode($JSONString);
	 	$this->SendDebug("ReceiveData", $data, 0);
 	}
	    
	public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    	{
		switch ($Message) {
			case 10001:
				// IPS_KERNELSTARTED
				$this->ApplyChanges();
				break;
			
		}
    	} 
	
	// Beginn der Funktionen
	public function GetState()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {
			$this->GetData();
		}
	}   
	 
	public function GetData()
	{
		If ($this->ReadPropertyBoolean("Open") == true) {
			$Response = false;
			$StatusVariables = array();
			$StatusVariables = array(
					35851 => array("AussenTemperatur", 64, 1), 
					35852 => array("StatusCommand_1", 1, 0),
    					35862 => array("ResetAlarmrelais", 1, 0), 
					35887 => array("StatusAlarmrelais", 1, 0), 
    					35888 => array("StatusCommand_2", 1, 0), 
					35901 => array("Schornsteinfegerfunktion", 1, 0),
    					35903 => array("Brennerleistung", 1, 0),
					35904 => array("Handbetrieb", 1, 0),
					35905 => array("Reglerstoppfunktion", 1, 0),
					35906 => array("ReglerstoppSollwert", 1, 0),
					37981 => array("Wasserdruck", 10, 0),
					37982 => array("StatusCommand_3", 1, 0),
					);
			
			SetValueInteger($this->GetIDForIdent("LastUpdate"), time() );
			// {"DataID":"{E310B701-4AE7-458E-B618-EC13A1A6F6A8}","Function":4,"Address":1024,"Quantity":1,"Data":""}
			foreach ($StatusVariables as $Key => $Values) {
				$Function = 3;
				$Address = $Key;
				$Quantity = 1;
				$Name = $Values[0];
				$Devisor = floatval($Values[1]);
				$Signed = intval($Values[2]);
				$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => $Function, "Address" => $Address, "Quantity" => $Quantity, "Data" => ":")));
				$Result = (unpack("n*", substr($Result,2)));
				If (is_array($Result)) {
					If (count($Result) == 1) {
						$Response = $Result[1];
						
						If ($Signed == 0) {
							$Value = ($Response/$Devisor);
						}
						else {
							$Value = $this->bin16dec($Response/$Devisor);
						}
						
						$this->SendDebug("GetData", $Name.": ".$Value, 0);
						If ($this->GetValue($Name) <> $Value) {
							$this->SetValue($Name, $Value);
						}
					}
				}
			}
			$this->GetSystemDate();
		}
	}
	
	public function GetSystemDate()
	{
		If ($this->ReadPropertyBoolean("Open") == true) {
			$Response = false;
			$Systemdate = array();
			for ($Address = 39920; $Address <= 39926; $Address++) {
    				$Function = 3;
				$Quantity = 1;
				$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => $Function, "Address" => $Address, "Quantity" => $Quantity, "Data" => ":")));
				$Result = (unpack("n*", substr($Result,2)));
				If (is_array($Result)) {
					If (count($Result) == 1) {
						$Response = $Result[1];
						$this->SendDebug("GetSystemDate", $Address.": ".$Response, 0);
						$Systemdate[$Address] = $Response;
					}
				}	
			}
			$Result = mktime($Systemdate[39923], $Systemdate[39924], $Systemdate[39925], $Systemdate[39921], $Systemdate[39922], ($Systemdate[39920] + 1900));
			If (GetValue($this->GetIDForIdent("Systemzeit")) <> $Result) {
				SetValueInteger($this->GetIDForIdent("Systemzeit"), $Result);
			}
		}
	}  
	    
	public function SetData(int $Address, int $Payload)
	{
		If ($this->ReadPropertyBoolean("Open") == true) {
			$Function = 6;
			$Quantity = 1;
			$Address = $Address;
			$SendPayload = chr($Payload >> 8).chr($Payload & 255);
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => $Function, "Address" => $Address, "Quantity" => $Quantity, "Data" => utf8_encode($SendPayload) ), JSON_UNESCAPED_UNICODE ));
		}
	}
	    
	private function bin16dec($dec) 
	{
	    	// converts 16bit binary number string to integer using two's complement
	    	$BinString = decbin($dec);
		$DecNumber = bindec($BinString) & 0xFFFF; // only use bottom 16 bits
	    	If (0x8000 & $DecNumber) {
			$DecNumber = - (0x010000 - $DecNumber);
	    	}
	return $DecNumber;
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
	    
	private function GetParentID()
	{
		$ParentID = (IPS_GetInstance($this->InstanceID)['ConnectionID']);  
	return $ParentID;
	}
  	
  	private function GetParentStatus()
	{
		$Status = (IPS_GetInstance($this->GetParentID())['InstanceStatus']);  
	return $Status;
	}
	    
	protected function HasActiveParent()
    	{
		$Instance = @IPS_GetInstance($this->InstanceID);
		if ($Instance['ConnectionID'] > 0)
		{
			$Parent = IPS_GetInstance($Instance['ConnectionID']);
			if ($Parent['InstanceStatus'] == 102)
			return true;
		}
        return false;
    	}  
}
?>
