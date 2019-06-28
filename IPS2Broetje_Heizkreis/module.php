<?
    // Klassendefinition
    class IPS2Broetje_Heizkreis extends IPSModule 
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
		
		$this->ConnectParent("{A5F663AB-C400-4FE5-B207-4D67CC030564}");
	
            	$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyInteger("HeatingCircuit", 0);
		$this->RegisterPropertyInteger("Timer_1", 60);
		$this->RegisterTimer("Timer_1", 0, 'IPS2BroetjeHeizkreis_GetState($_IPS["TARGET"]);');
		
		
		//Status-Variablen anlegen
		
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
		$arrayElements[] = array("type" => "Label", "label" => "Wahl des Heizkreises (1-3)");
		$arrayOptions = array();
		$arrayOptions[] = array("label" => "Heizkreis 1", "value" => 0);
		$arrayOptions[] = array("label" => "Heizkreis 2", "value" => 3072);
		$arrayOptions[] = array("label" => "Heizkreis 3", "value" => 6144);
		$arrayElements[] = array("type" => "Select", "name" => "HeatingCircuit", "caption" => "Heizkreis", "options" => $arrayOptions );
		
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
		
		// Profile anlegen
		$this->RegisterProfileInteger("IPS2Broetje.OperatingMode", "Information", "", "", 0, 3, 0);
		IPS_SetVariableProfileAssociation("IPS2Broetje.OperatingMode", 0, "Schutzbetrieb", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.OperatingMode", 1, "Automatik", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.OperatingMode", 2, "Reduziert", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.OperatingMode", 3, "Komfort", "Information", -1);
		
		$this->RegisterProfileInteger("IPS2Broetje.RoomThermostat", "Information", "", "", 0, 3, 0);
		IPS_SetVariableProfileAssociation("IPS2Broetje.RoomThermostat", 0, "Kein Bedarf", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.RoomThermostat", 1, "Bedarf", "Information", -1);
		
		$this->RegisterProfileInteger("IPS2Broetje.Status", "Information", "", "", 0, 3, 0);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 0, "OK", "Information", 0x00FF00);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 1, "Inaktiv", "Alert", 0xFF0000);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 2, "Kurzschluß", "Alert", 0xFF0000);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 64, "Fehlerhaft", "Alert", 0xFF0000);
		
		$this->RegisterProfileFloat("IPS2Broetje.TemperatureSetpoint", "Temperature", "", " °C", 4, 35, 0.1, 1);
		
		$this->RegisterProfileFloat("IPS2Broetje.SummerWinter", "Temperature", "", " °C", 8, 30, 0.1, 1);
		
		$this->RegisterProfileFloat("IPS2Broetje.CharacteristicCurveShift", "Temperature", "", " °C", -4.5, 4.5, 0.1, 1);
		
		$this->RegisterProfileFloat("IPS2Broetje.CharacteristicSlope", "Temperature", "", " °C", 0.1, 4, 0.1, 1);
		
		$this->RegisterProfileFloat("IPS2Broetje.HeatingLimit", "Temperature", "", " °C", -10, 10, 0.1, 1);
		
		$this->RegisterProfileFloat("IPS2Broetje.Preheat", "Temperature", "", " °C", 8, 95, 0.1, 1);
		
		$this->RegisterProfileFloat("IPS2Broetje.RoomTemperature", "Temperature", "", " °C", 0, 50, 0.1, 1);
		
		$this->RegisterProfileFloat("IPS2Broetje.PreheatTemperature", "Temperature", "", " °C", 0, 140, 0.1, 1);
		
		// Status-Variablen anlegen
		$this->RegisterVariableInteger("LastUpdate", "Letztes Update", "~UnixTimestamp", 5);
		
		$this->RegisterVariableInteger("Betriebsart", "Betriebsart", "IPS2Broetje.OperatingMode", 10);
		$this->EnableAction("Betriebsart");
		
		$this->RegisterVariableFloat("Komfortsollwert", "Komfort-Sollwert", "IPS2Broetje.TemperatureSetpoint", 20);
		$this->EnableAction("Komfortsollwert");
		
		$this->RegisterVariableFloat("Reduziertsollwert", "Reduzierter-Sollwert", "IPS2Broetje.TemperatureSetpoint", 30);
		$this->EnableAction("Reduziertsollwert");
		
		$this->RegisterVariableFloat("Frostschutzsollwert", "Frostschutz-Sollwert", "IPS2Broetje.TemperatureSetpoint", 40);
		$this->EnableAction("Frostschutzsollwert");
		
		$this->RegisterVariableFloat("KennlinieSteilheit", "Kennlinie Steilheit", "IPS2Broetje.CharacteristicSlope", 50);
		$this->EnableAction("KennlinieSteilheit");
		
		$this->RegisterVariableFloat("KennlinieVerschiebung", "Kennlinien Verschiebung", "IPS2Broetje.CharacteristicCurveShift", 60);
		$this->EnableAction("KennlinieVerschiebung");
		
		$this->RegisterVariableFloat("SommerWinterheizgrenze", "Sommer-/Winterheizgrenze", "IPS2Broetje.SummerWinter", 70);
		$this->EnableAction("SommerWinterheizgrenze");
		
		$this->RegisterVariableInteger("StatusCommand_1", "Status Sommer-/Winterheizgrenze", "IPS2Broetje.Status", 80);
		$this->EnableAction("StatusCommand_1");
		
		$this->RegisterVariableFloat("Tagesheizgrenze", "Tagesheizgrenze", "~IPS2Broetje.HeatingLimit", 90);
		$this->EnableAction("Tagesheizgrenze");
		
		$this->RegisterVariableInteger("StatusCommand_2", "Status Tagesheizgrenze", "IPS2Broetje.Status", 100);
		$this->EnableAction("StatusCommand_2");
		
		$this->RegisterVariableFloat("VorlaufsollwertMinimum", "Vorlaufsollwert Minimum", "IPS2Broetje.Preheat", 110);
		$this->EnableAction("VorlaufsollwertMinimum");
		
		$this->RegisterVariableFloat("VorlaufsollwertMaximum", "Vorlaufsollwert Maximum", "IPS2Broetje.Preheat", 120);
		$this->EnableAction("VorlaufsollwertMaximum");
		
		$this->RegisterVariableFloat("VorlaufsollwertRaumthermostat", "Vorlaufsollwert Raumthermostat", "IPS2Broetje.Preheat", 130);
		$this->EnableAction("VorlaufsollwertRaumthermostat");
		
		$this->RegisterVariableInteger("StatusCommand_3", "Status Vorlaufsollwert Raumthermostat", "IPS2Broetje.Status", 140);
		$this->EnableAction("StatusCommand_3");
		
		$this->RegisterVariableInteger("Raumeinfluss", "Raumeinfluss", "~Intensity.100", 150);
           	$this->EnableAction("Raumeinfluss");
		
		$this->RegisterVariableInteger("StatusCommand_4", "Status Sensor Raumeinfluss", "IPS2Broetje.Status", 160);
		$this->EnableAction("StatusCommand_4");
		
		$this->RegisterVariableFloat("Raumtemperatur", "Raumtemperatur", "IPS2Broetje.RoomTemperature", 170);
		$this->RegisterVariableInteger("Status_5", "Status Sensor Raumtemperatur", "IPS2Broetje.Status", 180);
		
		$this->RegisterVariableFloat("Raumsollwert", "Raum-Sollwert", "IPS2Broetje.TemperatureSetpoint", 190);
		$this->RegisterVariableInteger("Status_6", "Status Sensor Raum-Sollwert", "IPS2Broetje.Status", 200);
		
		$this->RegisterVariableFloat("Vorlauftemperatur", "Vorlauftemperatur", "IPS2Broetje.PreheatTemperature", 210);
		$this->RegisterVariableInteger("Status_7", "Status Sensor Vorlauftemperatur", "IPS2Broetje.Status", 220);
		
		$this->RegisterVariableFloat("Vorlaufsollwert", "Vorlauf-Sollwert", "IPS2Broetje.PreheatTemperature", 230);
		$this->RegisterVariableInteger("Status_8", "Status Sensor Vorlauf-Sollwert", "IPS2Broetje.Status", 240);
		
		$this->RegisterVariableInteger("Raumthermostat", "Raumthermostat", "IPS2Broetje.RoomThermostat", 250); 
		$this->RegisterVariableInteger("Status_9", "Status Raumthermostat", "IPS2Broetje.Status", 260);
		
		$this->RegisterVariableInteger("StatusHeizkreis", "Status Heizkreis", "", 270);
		
		$this->RegisterVariableBoolean("Heizkreis", "Heizkreis", "~Switch", 280);
	        $this->EnableAction("Heizkreis");
		
		$this->RegisterVariableFloat("Mischerueberhoehung", "Mischerüberhöhung", "IPS2Broetje.RoomTemperature", 290);
		$this->EnableAction("Mischerueberhoehung");
		
		$this->RegisterVariableInteger("PumpendrehzahlMinimum_1", "Pumpendrehzahl Minimum", "~Intensity.100", 300);
           	$this->EnableAction("PumpendrehzahlMinimum_1");
		
		$this->RegisterVariableInteger("PumpendrehzahlMaximum_1", "Pumpendrehzahl Maximum", "~Intensity.100", 310);
           	$this->EnableAction("PumpendrehzahlMaximum_1");
		
		$this->RegisterVariableBoolean("Heizkreispumpe", "Heizkreispumpe", "~Switch", 320);
		$this->RegisterVariableInteger("Status_10", "Status Heizkreispumpe", "IPS2Broetje.Status", 330);
		
		$this->RegisterVariableBoolean("HeizkreismischerAuf", "Heizkreismischer Auf", "~Switch", 340);
		$this->RegisterVariableInteger("Status_11", "Status Heizkreismischer Auf", "IPS2Broetje.Status", 350);
		
		$this->RegisterVariableBoolean("HeizkreismischerZu", "Heizkreismischer Zu", "~Switch", 360);
		$this->RegisterVariableInteger("Status_12", "Status Heizkreismischer Zu", "IPS2Broetje.Status", 370);
		
		$this->RegisterVariableInteger("DrehzahlHeizkreispumpe", "Drehzahl Heizkreispumpe", "~Intensity.100", 380);
		$this->RegisterVariableInteger("Status_13", "Status Drehzahlsensor", "IPS2Broetje.Status", 390);
		
		$this->RegisterVariableInteger("PumpendrehzahlMinimum", "Pumpendrehzahl Minimum", "~Intensity.100", 390);
           	$this->EnableAction("PumpendrehzahlMinimum");
		
		$this->RegisterVariableInteger("PumpendrehzahlMaximum", "Pumpendrehzahl Maximum", "~Intensity.100", 400);
           	$this->EnableAction("PumpendrehzahlMaximum");
		
		If ($this->ReadPropertyBoolean("Open") == true) {
			$this->GetState();
			$this->SetStatus(102);
			$this->SetTimerInterval("Timer_1", $this->ReadPropertyInteger("Timer_1") );
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
			case "Betriebsart":
				$this->SetData(1024, $Value);
			    break;
			case "Komfortsollwert":
				$this->SetData(1025, intval($Value * 64));
			    break;
			case "Reduziertsollwert":
				$this->SetData(1026, intval($Value * 64));
			    break;
			case "Frostschutzsollwert":
				$this->SetData(1027, intval($Value * 64));
			    break;
			case "KennlinieSteilheit":
				$this->SetData(1028, ($Value * 50));
			    break;
			case "KennlinieVerschiebung":
				$this->SetData(1029, ($Value * 64));
			    break;
			case "SommerWinterheizgrenze":
				$this->SetData(1030, ($Value * 64));
			    break;
			case "Tagesheizgrenze":
				$this->SetData(1032, ($Value * 64));
			    break;
			case "VorlaufsollwertMinimum":
				$this->SetData(1034, ($Value * 64));
			    break;
			case "VorlaufsollwertMaximum":
				$this->SetData(1035, ($Value * 64));
			    break;
			case "VorlaufsollwertRaumthermostat":
				$this->SetData(1036, ($Value * 64));
			    break;
			case "Raumeinfluss":
				$this->SetData(1038, $Value);
			    break;
			case "Heizkreis":
				$this->SetData(1055, $Value);
			    break;
			case "Mischerueberhoehung":
				$this->SetData(1077, ($Value * 64));
			    break;
			case "PumpendrehzahlMinimum":
				$this->SetData(1128, $Value);
			    break;
			case "PumpendrehzahlMaximum":
				$this->SetData(1129, $Value);
			    break;
			default:
			    throw new Exception("Invalid Ident");
			}
			$this->GetData();
		}
	}
	    
	public function ReceiveData($JSONString) 
	{
	    	// Empfangene Daten vom Gateway/Splitter
	    	$data = json_decode($JSONString);
	 	$this->SendDebug("ReceiveData", $data, 0);
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
					1024 => array("Betriebsart", 1, 0), 
					1025 => array("Komfortsollwert", 64, 0),
    					1026 => array("Reduziertsollwert", 64, 0), 
					1027 => array("Frostschutzsollwert", 64, 0), 
    					1028 => array("KennlinieSteilheit", 50, 0), 
					1029 => array("KennlinieVerschiebung", 64, 1),
    					1030 => array("SommerWinterheizgrenze", 64, 0),
					1031 => array("StatusCommand_1", 1, 0),
					1032 => array("Tagesheizgrenze", 64, 1),
					1033 => array("StatusCommand_2", 1, 0),
					1034 => array("VorlaufsollwertMinimum", 64, 0), 
					1035 => array("VorlaufsollwertMaximum", 64, 0), 
					1036 => array("VorlaufsollwertRaumthermostat", 64, 0), 
					1037 => array("StatusCommand_3", 1, 0),
					1038 => array("Raumeinfluss", 1, 0), 
					1039 => array("StatusCommand_4", 1, 0),
					1042 => array("Raumtemperatur", 64, 0),
					1043 => array("Status_5", 1, 0),
					1044 => array("Raumsollwert", 64, 0),
					1045 => array("Status_6", 1, 0),
					1046 => array("Vorlauftemperatur", 64, 0),
					1047 => array("Status_7", 1, 0),
					1048 => array("Vorlaufsollwert", 64, 0),
					1049 => array("Status_8", 1, 0),
					1050 => array("Raumthermostat", 1, 0),
					1051 => array("Status_9", 1, 0),
					1054 => array("StatusHeizkreis", 1, 0),
					1055 => array("Heizkreis", 1, 0),
					1077 => array("Mischerueberhoehung", 64, 0),
					1095 => array("Heizkreispumpe", 1, 0),
					1096 => array("Status_10", 1, 0),
					1097 => array("HeizkreismischerAuf", 1, 0),
					1098 => array("Status_11", 1, 0),
					1099 => array("HeizkreismischerZu", 1, 0),
					1100 => array("Status_12", 1, 0),
					1101 => array("DrehzahlHeizkreispumpe", 1, 0),
					1102 => array("Status_13", 1, 0),
					1128 => array("PumpendrehzahlMinimum", 1, 0),
					1129 => array("PumpendrehzahlMaximum", 1, 0),
					);
			
			SetValueInteger($this->GetIDForIdent("LastUpdate"), time() );
			// {"DataID":"{E310B701-4AE7-458E-B618-EC13A1A6F6A8}","Function":4,"Address":1024,"Quantity":1,"Data":""}
			foreach ($StatusVariables as $Key => $Values) {
				$HeatingCircuit = $this->ReadPropertyInteger("HeatingCircuit");
				$Function = 3;
				$Address = ($Key + $HeatingCircuit);
				$Quantity = 1;
				$Name = $Values[0];
				$Devisor = intval($Values[1]);
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
						If (GetValue($this->GetIDForIdent($Name)) <> $Value) {
							SetValue($this->GetIDForIdent($Name), $Value);
						}					
					}
				}
			}
		}
	}
	
	public function SetData(int $Address, int $Payload)
	{
		If ($this->ReadPropertyBoolean("Open") == true) {
			$HeatingCircuit = $this->ReadPropertyInteger("HeatingCircuit");
			$Function = 6;
			$Quantity = 1;
			$Address = $Address + $HeatingCircuit;
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
