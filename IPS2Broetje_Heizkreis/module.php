
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
              $this->RequireParent("{A5F663AB-C400-4FE5-B207-4D67CC030564}");
	
            	$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyInteger("Output", 1);
		$this->RegisterPropertyInteger("Address", 0);
		$this->RegisterPropertyInteger("Bit", 0);
		$this->RegisterPropertyInteger("Switchtime", 20);
		$this->RegisterPropertyInteger("Timer_1", 250);
		$this->RegisterTimer("Timer_1", 0, 'I2LTaster_GetState($_IPS["TARGET"]);');
		$this->RegisterPropertyBoolean("AP", false); // Parallele automatische Progamme
		$this->RegisterPropertyInteger("Output_AP", 1);
		
		//Status-Variablen anlegen
		$this->RegisterVariableBoolean("State", "State", "~Switch", 10);
		$this->EnableAction("State");
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
		$arrayElements[] = array("type" => "Label", "label" => "Auswahl des Netzwerkeingangs"); 
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Address",  "caption" => "Adresse"); 
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Bit",  "caption" => "Bit"); 
		$arrayElements[] = array("type" => "Label", "label" => "Laufzeit des Tast-Impulses");
		$arrayElements[] = array("type" => "IntervalBox", "name" => "Switchtime", "caption" => "ms");
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________"); 
		$arrayElements[] = array("type" => "Label", "label" => "Auswahl des digitalen Ausgangs oder Merkers"); 
		$arrayOptions = array();
		for ($i = 1; $i <= 16; $i++) {
		    	$arrayOptions[] = array("label" => "Q".$i, "value" => $i);
		}
		for ($i = 1; $i <= 27; $i++) {
		    	$arrayOptions[] = array("label" => "M".$i, "value" => ($i + 100));
		}
		$arrayElements[] = array("type" => "Select", "name" => "Output", "caption" => "Ausgang", "options" => $arrayOptions );
		$arrayElements[] = array("type" => "IntervalBox", "name" => "Timer_1", "caption" => "ms");
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "label" => "Status parallel laufender automatischer Programme"); 
		$arrayElements[] = array("name" => "AP", "type" => "CheckBox",  "caption" => "Aktiv"); 
		$arrayElements[] = array("type" => "Label", "label" => "Auswahl des digitalen Ausgangs oder Merkers"); 
		$arrayOptions = array();
		for ($i = 1; $i <= 16; $i++) {
		    	$arrayOptions[] = array("label" => "Q".$i, "value" => $i);
		}
		for ($i = 1; $i <= 27; $i++) {
		    	$arrayOptions[] = array("label" => "M".$i, "value" => ($i + 100));
		}
		$arrayElements[] = array("type" => "Select", "name" => "Output_AP", "caption" => "Ausgang", "options" => $arrayOptions );
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
 	} 
	
	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
                // Diese Zeile nicht löschen
                parent::ApplyChanges();
		
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
	public function SetState(Bool $State)
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {
			$this->SendDebug("SetState", "Ausfuehrung", 0);
			$Area = 132; // Konstante
			$Address = $this->ReadPropertyInteger("Address");
			$Bit = $this->ReadPropertyInteger("Bit");
			$AddressBit = ($Address * 10) + $Bit;
			$AddressBit = intval(octdec($AddressBit));
			If ($State == true) {
				$DataPayload = utf8_encode(chr(1));
			}
			else {
				$DataPayload = utf8_encode(chr(0));
			}
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{042EF3A2-ECF4-404B-9FA2-42BA032F4A56}", "Function" => 5, "Area" => $Area, "AreaAddress" => 0, "BitAddress" => $AddressBit, "WordLength" => 1,"DataCount" => 1,"DataPayload" => $DataPayload)));
			//$this->SendDebug("SetState", "Ergebnis: ".intval($Result), 0);
			$this->GetState();
		}
	}
	    
	public function GetState()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {
			//$this->SendDebug("GetState", "Ausfuehrung", 0);
			$Output = $this->ReadPropertyInteger("Output");
			$AreaAddress = 0;
			
			If ($Output < 100) {
				$Area = 130; // Ausgang
				$BitAddress = $Output - 1;
			}
			else {
				$Area = 131; // Merker
				$BitAddress = $Output - 101;
			}
				
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{042EF3A2-ECF4-404B-9FA2-42BA032F4A56}", "Function" => 4, "Area" => $Area, "AreaAddress" => $AreaAddress, "BitAddress" => $BitAddress, "WordLength" => 1, "DataCount" => 1,"DataPayload" => "")));
			If ($Result === false) {
				$this->SetStatus(202);
				$this->SendDebug("GetState", "Fehler bei der Ausführung!", 0);
			}
			else {
				$this->SetStatus(102);
				$State = ord($Result);
				//$this->SendDebug("GetState", "Ergebnis: ".$State, 0);
				If ($State <> GetValueBoolean($this->GetIDForIdent("State"))) {
					SetValueBoolean($this->GetIDForIdent("State"), $State);
				}
				$ReadAP = $this->ReadPropertyBoolean("AP");
				If ($ReadAP == true) {
					 $this->GetAPState();
				}
			}
		}
	}
	    
	public function GetAPState()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {
			//$this->SendDebug("GetAPState", "Ausfuehrung", 0);
			$Output = $this->ReadPropertyInteger("Output_AP");
			$AreaAddress = 0;
			
			If ($Output < 100) {
				$Area = 130; // Ausgang
				$BitAddress = $Output - 1;
			}
			else {
				$Area = 131; // Merker
				$BitAddress = $Output - 101;
			}
				
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{042EF3A2-ECF4-404B-9FA2-42BA032F4A56}", "Function" => 4, "Area" => $Area, "AreaAddress" => $AreaAddress, "BitAddress" => $BitAddress, "WordLength" => 1, "DataCount" => 1,"DataPayload" => "")));
			If ($Result === false) {
				$this->SetStatus(202);
				$this->SendDebug("GetState", "Fehler bei der Ausführung!", 0);
			}
			else {
				$this->SetStatus(102);
				$State = ord($Result);
				//$this->SendDebug("GetAPState", "Ergebnis: ".$State, 0);
				If ($State == false) {
					$this->EnableAction("State");
				}
				else {
					$this->DisableAction("State");
				}
			}
		}
	}
	    
	public function Keypress()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {	
			$Switchtime = $this->ReadPropertyInteger("Switchtime"); // Dauer der Betätigung
			$this->SetState(true);
			IPS_Sleep($Switchtime);
			$this->SetState(false);
		}
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
