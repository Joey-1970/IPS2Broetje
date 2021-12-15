<?
    // Klassendefinition
    class IPS2Broetje_Wasserspeicher extends IPSModule 
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
		$this->RegisterTimer("Timer_1", 0, 'IPS2BroetjeWasserspeicher_GetState($_IPS["TARGET"]);');
		
		
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
		$this->RegisterProfileInteger("IPS2Broetje.Status", "Information", "", "", 0, 3, 0);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 0, "OK", "Information", 0x00FF00);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 1, "Inaktiv", "Alert", 0xFF0000);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 2, "Kurzschluß", "Alert", 0xFF0000);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 64, "Fehlerhaft", "Alert", 0xFF0000);
		
		$this->RegisterProfileInteger("IPS2Broetje.Minuten", "Clock", "", " min", 0, 360, 1);
		
		// Status-Variablen anlegen
		$this->RegisterVariableInteger("LastUpdate", "Letztes Update", "~UnixTimestamp", 5);
		
		$this->RegisterVariableFloat("Trinkwassertemperatur_1", "Trinkwassertemperatur 1", "~Temperature", 10);
		$this->RegisterVariableInteger("StatusCommand_1", "Status Sensor Trinkwassertemperatur 1", "IPS2Broetje.Status", 20);
		
		$this->RegisterVariableFloat("Trinkwassertemperatur_2", "Trinkwassertemperatur 2", "~Temperature", 30);
		$this->RegisterVariableInteger("StatusCommand_2", "Status Sensor Trinkwassertemperatur 2", "IPS2Broetje.Status", 40);
		
		$this->RegisterVariableInteger("Ladezeitbegrenzung", "Ladezeitbegrenzung", "IPS2Broetje.Minuten", 50);
		$this->EnableAction("Ladezeitbegrenzung");
		$this->RegisterVariableInteger("StatusCommand_3", "Status Ladezeitbegrenzung", "IPS2Broetje.Status", 60);
		$this->EnableAction("StatusCommand_3");
		
		$this->RegisterVariableFloat("Vorlaufsollwertueberhoehung", "Vorlaufsollwertüberhöhung", "~Temperature", 70);
		$this->EnableAction("Vorlaufsollwertueberhoehung");
		
		$this->RegisterVariableFloat("Schaltdifferenz", "Schaltdifferenz", "~Temperature", 80);
		$this->EnableAction("Schaltdifferenz");
		
		$this->RegisterVariableFloat("LadetemperaturMaximum", "Ladetemperatur Maximum", "~Temperature", 90);
		$this->EnableAction("LadetemperaturMaximum");
		
		$this->RegisterVariableBoolean("Trinkwasserpumpe", "Trinkwasserpumpe", "~Switch", 100);
		$this->RegisterVariableInteger("StatusCommand_4", "Status Trinkwasserpumpe", "IPS2Broetje.Status", 110);
		
		$this->RegisterVariableInteger("DrehzahlTrinkwasserpumpe", "Drehzahl Trinkwasserpumpe", "~Intensity.100", 120);
		$this->RegisterVariableInteger("StatusCommand_5", "Status Drehzahl Trinkwasserpumpe", "IPS2Broetje.Status", 130);
		
		$this->RegisterVariableInteger("DrehzahlTWW", "Drehzahl TWW", "~Intensity.100", 140);
		$this->RegisterVariableInteger("StatusCommand_6", "Status Drehzahl TWW", "IPS2Broetje.Status", 150);
		
		$this->RegisterVariableFloat("Trinkwassersollwert", "Trinkwasser-Sollwert", "~Temperature", 160);
		$this->RegisterVariableInteger("StatusCommand_7", "Status Trinkwasser-Sollwert", "IPS2Broetje.Status", 170);
		
		$this->RegisterVariableFloat("TWWZirkulationstemperatur", "TWW Zirkulationstemperatur", "~Temperature", 180);
		$this->RegisterVariableInteger("StatusCommand_8", "Status TWW Zirkulationstemperatur", "IPS2Broetje.Status", 190);
		
		$this->RegisterVariableFloat("TWWLadetemperatur", "TWW Ladetemperatur", "~Temperature", 200);
		$this->RegisterVariableInteger("StatusCommand_9", "Status TWW Ladetemperatur", "IPS2Broetje.Status", 210);
		
		$this->RegisterVariableBoolean("ZustandZirkulationspumpe", "Zustand Zirkulationspumpe", "~Switch", 220);
		$this->RegisterVariableInteger("StatusCommand_10", "Status Zirkulationspumpe", "IPS2Broetje.Status", 230);
		
		$this->RegisterVariableBoolean("TWWZwischenkreispumpe", "Zustand TWW Zwischenkreispumpe", "~Switch", 240);
		$this->RegisterVariableInteger("StatusCommand_11", "Status TWW Zwischenkreispumpe", "IPS2Broetje.Status", 250);
		
		$this->RegisterVariableBoolean("BAUmschaltungTWW", "BAUmschaltungTWW", "~Switch", 260);
		
		
		If ((IPS_GetKernelRunlevel() == KR_READY) AND ($this->ReadPropertyBoolean("Open") == true)) {
			$this->GetState();
			If ($this->GetStatus() <> 102) {
				$this->SetStatus(102);
			}
			$this->SetTimerInterval("Timer_1", $this->ReadPropertyInteger("Timer_1") );
		}
		else {
			If ($this->GetStatus() <> 104) {
				$this->SetStatus(104);
			}
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
	    
	public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    	{
		switch ($Message) {
			case IPS_KERNELSTARTED:
				// IPS_KERNELSTARTED
				If ($this->ReadPropertyBoolean("Open") == true) {
					$this->GetState();
					If ($this->GetStatus() <> 102) {
						$this->SetStatus(102);
					}
					$this->SetTimerInterval("Timer_1", $this->ReadPropertyInteger("Timer_1") );
				}
				else {
					If ($this->GetStatus() <> 104) {
						$this->SetStatus(104);
					}
					$this->SetTimerInterval("Timer_1", 0);
				}
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
					11264 => array("Trinkwassertemperatur_1", 64), 
					11265 => array("StatusCommand_1", 1),
    					11266 => array("Trinkwassertemperatur_2", 64), 
					11267 => array("StatusCommand_2", 1), 
    					11280 => array("Ladezeitbegrenzung", 1), 
					11281 => array("StatusCommand_3", 1),
    					11290 => array("Vorlaufsollwertueberhoehung", 64),
					11294 => array("Schaltdifferenz", 64),
					11299 => array("LadetemperaturMaximum", 64),
					11369 => array("Trinkwasserpumpe", 1),
					11370 => array("StatusCommand_4", 1), 
					11373 => array("DrehzahlTrinkwasserpumpe", 1), 
					11374 => array("StatusCommand_5", 1), 
					11375 => array("DrehzahlTWW", 1),
					11376 => array("StatusCommand_6", 1),
					11379 => array("Trinkwassersollwert", 64),
					11380 => array("StatusCommand_7", 1), 
					11381 => array("TWWZirkulationstemperatur", 64), 
					11382 => array("StatusCommand_8", 1), 
					11383 => array("TWWLadetemperatur", 64),
					11384 => array("StatusCommand_9", 1),
					11395 => array("ZustandZirkulationspumpe", 1),
					11396 => array("StatusCommand_10", 1),
					11411 => array("TWWZwischenkreispumpe", 1), 
					11412 => array("StatusCommand_11", 1), 
					11419 => array("BAUmschaltungTWW", 1),
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
}
?>
