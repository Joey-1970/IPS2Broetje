<?
    // Klassendefinition
    class IPS2Broetje_Wasser extends IPSModule 
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
		$this->RegisterPropertyInteger("Timer_1", 60);
		$this->RegisterTimer("Timer_1", 0, 'IPS2BroetjeWasser_GetState($_IPS["TARGET"]);');
		
		
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
		$this->RegisterProfileInteger("IPS2Broetje.OperatingModeWater", "Information", "", "", 0, 2, 1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.OperatingModeWater", 0, "Aus", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.OperatingModeWater", 1, "Ein", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.OperatingModeWater", 2, "Eco", "Information", -1);
		
		$this->RegisterProfileInteger("IPS2Broetje.Release", "Information", "", "", 0, 2, 1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Release", 0, "24h/Tagh", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Release", 1, "Zeitprogramme Heizkreise", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Release", 2, "Zeitprogramm 4/TWW", "Information", -1);
		
		$this->RegisterProfileInteger("IPS2Broetje.LegionellaFunction", "Information", "", "", 0, 2, 1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaFunction", 0, "Aus", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaFunction", 1, "Periodisch", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaFunction", 2, "Fixer Wochentag", "Information", -1);
		
		$this->RegisterProfileInteger("IPS2Broetje.LegionellaWeekday", "Information", "", "", 1, 7, 1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaWeekday", 1, "Montag", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaWeekday", 2, "Dienstag", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaWeekday", 3, "Mittwoch", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaWeekday", 4, "Donnerstag", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaWeekday", 5, "Freitag", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaWeekday", 6, "Samstag", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaWeekday", 7, "Sonntag", "Information", -1);
		
		// Status-Variablen anlegen
		$this->RegisterVariableInteger("LastUpdate", "Letztes Update", "~UnixTimestamp", 5);
		
		$this->RegisterVariableInteger("Betriebsart", "Betriebsart", "IPS2Broetje.OperatingMode", 10);
		$this->EnableAction("Betriebsart");
		
		$this->RegisterVariableFloat("Nennsollwert", "Nenn-Sollwert", "~Temperature", 20);
		$this->EnableAction("Nennsollwert");
		
		$this->RegisterVariableFloat("Reduziertsollwert", "Reduzierter-Sollwert", "~Temperature", 30);
		$this->EnableAction("Reduziertsollwert");
		
		$this->RegisterVariableFloat("Freigabe", "Freigabe", "IPS2Broetje.Release", 40);
		$this->EnableAction("Freigabe");
		
		$this->RegisterVariableFloat("LegionellenFunktion", "Legionellen Funktion", "IPS2Broetje.LegionellaFunction", 50);
		$this->EnableAction("LegionellenFunktion");
		
		
		
		
		$this->RegisterVariableFloat("KennlinieVerschiebung", "Kennlinien Verschiebung", "~Temperature", 60);
		$this->EnableAction("KennlinieVerschiebung");
		
		$this->RegisterVariableFloat("SommerWinterheizgrenze", "Sommer-/Winterheizgrenze", "~Temperature", 70);
		$this->EnableAction("SommerWinterheizgrenze");
		
		$this->RegisterVariableInteger("StatusCommand_1", "Status/Command 1", "", 80);
		$this->EnableAction("StatusCommand_1");
		
		$this->RegisterVariableFloat("Tagesheizgrenze", "Tagesheizgrenze", "~Temperature", 90);
		$this->EnableAction("Tagesheizgrenze");
		
		$this->RegisterVariableInteger("StatusCommand_2", "Status/Command 2", "", 100);
		$this->EnableAction("StatusCommand_2");
		
		$this->RegisterVariableFloat("VorlaufsollwertMinimum", "Vorlaufsollwert Minimum", "~Temperature", 110);
		$this->EnableAction("VorlaufsollwertMinimum");
		
		$this->RegisterVariableFloat("VorlaufsollwertMaximum", "Vorlaufsollwert Maximum", "~Temperature", 120);
		$this->EnableAction("VorlaufsollwertMaximum");
		
		$this->RegisterVariableFloat("VorlaufsollwertRaumthermostat", "Vorlaufsollwert Raumthermostat", "~Temperature", 130);
		$this->EnableAction("VorlaufsollwertRaumthermostat");
		
		$this->RegisterVariableInteger("StatusCommand_3", "Status/Command 3", "", 140);
		$this->EnableAction("StatusCommand_3");
		
		$this->RegisterVariableInteger("Raumeinfluss", "Raumeinfluss", "~Intensity.100", 150);
           	$this->EnableAction("Raumeinfluss");
		
		$this->RegisterVariableInteger("StatusCommand_4", "Status/Command 4", "", 160);
		$this->EnableAction("StatusCommand_4");
		
		$this->RegisterVariableFloat("Raumtemperatur", "Raumtemperatur", "~Temperature", 170);
		$this->RegisterVariableInteger("Status_5", "Status 5", "", 180);
		
		$this->RegisterVariableFloat("Raumsollwert", "Raum-Sollwert", "~Temperature", 190);
		$this->RegisterVariableInteger("Status_6", "Status 6", "", 200);
		
		$this->RegisterVariableFloat("Vorlauftemperatur", "Vorlauftemperatur", "~Temperature", 210);
		$this->RegisterVariableInteger("Status_7", "Status 7", "", 220);
		
		$this->RegisterVariableFloat("Vorlaufsollwert", "Vorlauf-Sollwert", "~Temperature", 230);
		$this->RegisterVariableInteger("Status_8", "Status 8", "", 240);
		
		$this->RegisterVariableInteger("Raumthermostat", "Raumthermostat", "IPS2Broetje.RoomThermostat", 250); 
		$this->RegisterVariableInteger("Status_9", "Status 9", "", 260);
		
		$this->RegisterVariableInteger("StatusHeizkreis", "Status Heizkreis", "", 270);
		
		$this->RegisterVariableBoolean("Heizkreis", "Heizkreis", "~Switch", 280);
	        $this->EnableAction("Heizkreis");
		
		$this->RegisterVariableFloat("Mischerueberhoehung", "Mischerüberhöhung", "~Temperature", 290);
		$this->EnableAction("Mischerueberhoehung");
		
		$this->RegisterVariableInteger("PumpendrehzahlMinimum_1", "Pumpendrehzahl Minimum", "~Intensity.100", 300);
           	$this->EnableAction("PumpendrehzahlMinimum_1");
		
		$this->RegisterVariableInteger("PumpendrehzahlMaximum_1", "Pumpendrehzahl Maximum", "~Intensity.100", 310);
           	$this->EnableAction("PumpendrehzahlMaximum_1");
		
		$this->RegisterVariableBoolean("Heizkreispumpe", "Heizkreispumpe", "~Switch", 320);
		$this->RegisterVariableInteger("Status_10", "Status 10", "", 330);
		
		$this->RegisterVariableBoolean("HeizkreismischerAuf", "Heizkreismischer Auf", "~Switch", 340);
		$this->RegisterVariableInteger("Status_11", "Status 11", "", 350);
		
		$this->RegisterVariableBoolean("HeizkreismischerZu", "Heizkreismischer Zu", "~Switch", 360);
		$this->RegisterVariableInteger("Status_12", "Status 12", "", 370);
		
		$this->RegisterVariableInteger("DrehzahlHeizkreispumpe", "Drehzahl Heizkreispumpe", "~Intensity.100", 380);
		$this->RegisterVariableInteger("Status_13", "Status 13", "", 390);
		
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
  		switch($Ident) {
	        case "State":
			If ($Value <> GetValueBoolean($this->GetIDForIdent("State"))) {
				$this->KeyPress();
			}
	            break;
	        default:
	            throw new Exception("Invalid Ident");
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
					1024 => array("Betriebsart", 1), 
					1025 => array("Komfortsollwert", 64),
    					1026 => array("Reduziertsollwert", 64), 
					1027 => array("Frostschutzsollwert", 64), 
    					1028 => array("KennlinieSteilheit", 50), 
					1029 => array("KennlinieVerschiebung", 64),
    					1030 => array("SommerWinterheizgrenze", 64),
					1031 => array("StatusCommand_1", 1),
					1032 => array("Tagesheizgrenze", 64),
					1033 => array("StatusCommand_2", 1),
					1034 => array("VorlaufsollwertMinimum", 64), 
					1035 => array("VorlaufsollwertMaximum", 64), 
					1036 => array("VorlaufsollwertRaumthermostat", 64), 
					1037 => array("StatusCommand_3", 1),
					1038 => array("Raumeinfluss", 1), 
					1039 => array("StatusCommand_4", 1),
					1042 => array("Raumtemperatur", 64),
					1043 => array("Status_5", 1),
					1044 => array("Raumsollwert", 64),
					1045 => array("Status_6", 1),
					1046 => array("Vorlauftemperatur", 64),
					1047 => array("Status_7", 1),
					1048 => array("Vorlaufsollwert", 64),
					1049 => array("Status_8", 1),
					1050 => array("Raumthermostat", 1),
					1051 => array("Status_9", 1),
					1054 => array("StatusHeizkreis", 1),
					1055 => array("Heizkreis", 1),
					1077 => array("Mischerueberhoehung", 64),
					1095 => array("Heizkreispumpe", 1),
					1096 => array("Status_10", 1),
					1097 => array("HeizkreismischerAuf", 1),
					1098 => array("Status_11", 1),
					1099 => array("HeizkreismischerZu", 1),
					1100 => array("Status_12", 1),
					1101 => array("DrehzahlHeizkreispumpe", 1),
					1102 => array("Status_13", 1),
					1128 => array("PumpendrehzahlMinimum", 1),
					1129 => array("PumpendrehzahlMaximum", 1),
					);
			
			SetValueInteger($this->GetIDForIdent("LastUpdate"), time() );
			// {"DataID":"{E310B701-4AE7-458E-B618-EC13A1A6F6A8}","Function":4,"Address":1024,"Quantity":1,"Data":""}
			foreach ($StatusVariables as $Key => $Values) {
				$Function = 3;
				$Address = $Key;
				$Quantity = 1;
				$Name = $Values[0];
				$Devisor = intval($Values[1]);
				$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => $Function, "Address" => $Address, "Quantity" => $Quantity, "Data" => ":")));
				$Result = (unpack("n*", substr($Result,2)));
				If (is_array($Result)) {
					If (count($Result) == 1) {
						$Response = $Result[1];
						$this->SendDebug("GetData", $Name.": ".($Response/$Devisor), 0);
						If (GetValue($this->GetIDForIdent($Name)) <> ($Response/$Devisor)) {
							SetValue($this->GetIDForIdent($Name), ($Response/$Devisor));
						}
					}
				}
			}
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
