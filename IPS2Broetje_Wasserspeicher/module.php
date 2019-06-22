<?
    // Klassendefinition
    class IPS2Broetje_Wasserspeicher extends IPSModule 
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
		
		$this->RegisterProfileInteger("IPS2Broetje.Status", "Information", "", "", 0, 3, 1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 0, "OK", "Information", 0x00FF00);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 1, "Inaktiv", "Alert", 0xFF0000);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 2, "Kurzschluß", "Alert", 0xFF0000);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 64, "Fehlerhaft", "Alert", 0xFF0000);
		
		$this->RegisterProfileInteger("IPS2Broetje.Minuten", "Clock", "", " min", 0, 360, 1);
		
		// Status-Variablen anlegen
		$this->RegisterVariableInteger("LastUpdate", "Letztes Update", "~UnixTimestamp", 5);
		
		$this->RegisterVariableInteger("Betriebsart", "Betriebsart", "IPS2Broetje.OperatingModeWater", 10);
		$this->EnableAction("Betriebsart");
		
		$this->RegisterVariableFloat("Nennsollwert", "Nenn-Sollwert", "~Temperature", 20);
		$this->EnableAction("Nennsollwert");
		
		$this->RegisterVariableFloat("Reduziertsollwert", "Reduzierter-Sollwert", "~Temperature", 30);
		$this->EnableAction("Reduziertsollwert");
		
		$this->RegisterVariableInteger("Freigabe", "Freigabe", "IPS2Broetje.Release", 40);
		$this->EnableAction("Freigabe");
		
		$this->RegisterVariableInteger("LegionellenFunktion", "Legionellen Funktion", "IPS2Broetje.LegionellaFunction", 50);
		$this->EnableAction("LegionellenFunktion");
		
		$this->RegisterVariableInteger("LegionellenFunktionPeriodisch", "Legionellen Funktion Periodisch", "", 60);
		$this->EnableAction("LegionellenFunktionPeriodisch");
		
		$this->RegisterVariableInteger("LegionellenFunktionWochentag", "Legionellen Funktion Wochentag", "IPS2Broetje.LegionellaWeekday", 70);
		$this->EnableAction("LegionellenFunktionWochentag");
		
		$this->RegisterVariableInteger("LegionellenFunktionZeitpunkt", "Legionellen Funktion Zeitpunkt", "~UnixTimestampTime", 80);
		$this->EnableAction("LegionellenFunktionZeitpunkt");
		
		$this->RegisterVariableInteger("StatusCommand_1", "Status Legionellenfunktion Zeitpunkt", "IPS2Broetje.Status", 90);
		$this->EnableAction("StatusCommand_1");
		
		$this->RegisterVariableFloat("Legionellenfunktionsollwert", "Legionellenfunktion-Sollwert", "~Temperature", 100);
		$this->EnableAction("Legionellenfunktionsollwert");
		
		$this->RegisterVariableInteger("LegionellenFunktionVerweildauer", "Legionellen Funktion Verweildauer", "IPS2Broetje.Minuten", 110);
		$this->EnableAction("LegionellenFunktionVerweildauer");
		
		$this->RegisterVariableInteger("StatusCommand_2", "Status Legionellenfunktion Verweildauer", "IPS2Broetje.Status", 120);
		$this->EnableAction("StatusCommand_2");
		
		$this->RegisterVariableFloat("Zirkulationssollwert", "Zirkulations-Sollwert", "~Temperature", 130);
		$this->EnableAction("Zirkulationssollwert");
		
		$this->RegisterVariableInteger("StatusTrinkwasser", "Status Trinkwasser", "", 140);
			
		
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
					10240 => array("Betriebsart", 1), 
					10241 => array("Nennsollwert", 64),
    					10242 => array("Reduziertsollwert", 64), 
					10243 => array("Freigabe", 1), 
    					10244 => array("LegionellenFunktion", 1), 
					10245 => array("LegionellenFunktionPeriodisch", 1),
    					10246 => array("LegionellenFunktionWochentag", 1),
					10247 => array("LegionellenFunktionZeitpunkt", 0.1),
					10248 => array("StatusCommand_1", 1),
					10249 => array("Legionellenfunktionsollwert", 64),
					10250 => array("LegionellenFunktionVerweildauer", 1), 
					10251 => array("StatusCommand_2", 1), 
					10263 => array("Zirkulationssollwert", 64), 
					10273 => array("StatusTrinkwasser", 1), 
					);
			
			SetValueInteger($this->GetIDForIdent("LastUpdate"), time() );
			// {"DataID":"{E310B701-4AE7-458E-B618-EC13A1A6F6A8}","Function":4,"Address":1024,"Quantity":1,"Data":""}
			foreach ($StatusVariables as $Key => $Values) {
				$Function = 3;
				$Address = $Key;
				$Quantity = 1;
				$Name = $Values[0];
				$Devisor = floatval($Values[1]);
				$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => $Function, "Address" => $Address, "Quantity" => $Quantity, "Data" => ":")));
				$Result = (unpack("n*", substr($Result,2)));
				If (is_array($Result)) {
					If (count($Result) == 1) {
						$Response = $Result[1];
						$this->SendDebug("GetData", $Name.": ".($Response/$Devisor), 0);
						If ($Name <> "LegionellenFunktionZeitpunkt") {
							If (GetValue($this->GetIDForIdent($Name)) <> ($Response/$Devisor)) {
								SetValue($this->GetIDForIdent($Name), ($Response/$Devisor));
							}
						}
						else {
							$Minutes = ($Response/$Devisor);
							If (GetValue($this->GetIDForIdent($Name)) <> mktime(0, $Minutes, 0)) {
								SetValue($this->GetIDForIdent($Name), mktime(0, $Minutes, 0));
							}
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
