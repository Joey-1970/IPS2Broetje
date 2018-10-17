
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
		this->RegisterProfileInteger("IPS2Broetje.OperatingMode", "Information", "", "", 0, 3, 1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.OperatingMode", 0, "Schutzbetrieb", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.OperatingMode", 1, "Automatik", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.OperatingMode", 2, "Reduziert", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.OperatingMode", 3, "Komfort", "Information", -1);
		
		// Status-Variablen anlegen
		$this->RegisterVariableInteger("Betriebsart", "Betriebsart", "IPS2Broetje.OperatingMode", 10);
		$this->EnableAction("Betriebsart");
		
		$this->RegisterVariableFloat("Komfortsollwert", "Komfortsollwert", "~Temperature", 20);
		$this->EnableAction("Komfortsollwert");
		
		$this->RegisterVariableFloat("Reduziertsollwert", "Reduziertsollwert", "~Temperature", 30);
		$this->EnableAction("Reduziertsollwert");
		
		$this->RegisterVariableFloat("Frostschutzsollwert", "Frostschutzsollwert", "~Temperature", 40);
		$this->EnableAction("Frostschutzsollwert");
		
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
			
		}
	}   
	 
	public function GetData(Int $Function, Int $Address, Int $Quantity)
	{
		If ($this->ReadPropertyBoolean("Open") == true) {
			$Response = false;
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", "Function" => $Function, "Address" => $Address, "Quantity" => $Quantity, "Data" => "")));
			$Result = (unpack("n*", substr($Result,2)));
			If (is_array($Result)) {
				If (count($Result) == 1) {
					$Response = $Result[1];
				}
			}
			return $Response;	
			
			
			/*
				$StatusVariables = array();
$StatusVariables = array(1024 => array("Betriebsart", 1), 1025 => array("Komfortsollwert", 64),
    1026 => array("Reduziertsollwert", 64), 1027 => array("Frostschutzsollwert", 64), 
    1028 => array("KennlinieSteilheit", 50), 1029 => array("KennlinieVerschiebung", 64)
    1030 => array("SommerWinterheizgrenze", 64), 1031 => array("StatusCommand", 64));

print_r($StatusVariables);
			
			*/
			
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
	    
	private function HasActiveParent()
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
