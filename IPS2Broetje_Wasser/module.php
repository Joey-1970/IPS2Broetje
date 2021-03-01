<?
    // Klassendefinition
    class IPS2Broetje_Wasser extends IPSModule 
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
		$this->RegisterProfileInteger("IPS2Broetje.OperatingModeWater", "Information", "", "", 0, 2, 0);
		IPS_SetVariableProfileAssociation("IPS2Broetje.OperatingModeWater", 0, "Aus", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.OperatingModeWater", 1, "Ein", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.OperatingModeWater", 2, "Eco", "Information", -1);
		
		$this->RegisterProfileInteger("IPS2Broetje.Release", "Information", "", "", 0, 2, 0);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Release", 0, "24h/Tagh", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Release", 1, "Zeitprogramme Heizkreise", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Release", 2, "Zeitprogramm 4/TWW", "Information", -1);
		
		$this->RegisterProfileInteger("IPS2Broetje.LegionellaFunction", "Information", "", "", 0, 2, 0);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaFunction", 0, "Aus", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaFunction", 1, "Periodisch", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaFunction", 2, "Fixer Wochentag", "Information", -1);
		
		$this->RegisterProfileInteger("IPS2Broetje.LegionellaWeekday", "Information", "", "", 1, 7, 0);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaWeekday", 1, "Montag", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaWeekday", 2, "Dienstag", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaWeekday", 3, "Mittwoch", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaWeekday", 4, "Donnerstag", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaWeekday", 5, "Freitag", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaWeekday", 6, "Samstag", "Information", -1);
		IPS_SetVariableProfileAssociation("IPS2Broetje.LegionellaWeekday", 7, "Sonntag", "Information", -1);
		
		$this->RegisterProfileInteger("IPS2Broetje.LegionellaPeriodically", "Information", "", "", 1, 7, 0);
		
		$this->RegisterProfileInteger("IPS2Broetje.Status", "Information", "", "", 0, 3, 0);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 0, "OK", "Information", 0x00FF00);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 1, "Inaktiv", "Alert", 0xFF0000);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 2, "Kurzschluß", "Alert", 0xFF0000);
		IPS_SetVariableProfileAssociation("IPS2Broetje.Status", 64, "Fehlerhaft", "Alert", 0xFF0000);
		
		$this->RegisterProfileInteger("IPS2Broetje.Minuten", "Clock", "", " min", 2, 360, 1);
		
		$this->RegisterProfileFloat("IPS2Broetje.WaterTemperature", "Temperature", "", " °C", 8, 80, 0.1, 1);
		
		$this->RegisterProfileFloat("IPS2Broetje.LegionellaTemperature", "Temperature", "", " °C", 55, 95, 0.1, 1);
		
		// Status-Variablen anlegen
		$this->RegisterVariableInteger("LastUpdate", "Letztes Update", "~UnixTimestamp", 5);
		
		$this->RegisterVariableInteger("Betriebsart", "Betriebsart", "IPS2Broetje.OperatingModeWater", 10);
		$this->EnableAction("Betriebsart");
		
		$this->RegisterVariableFloat("Nennsollwert", "Nenn-Sollwert", "IPS2Broetje.WaterTemperature", 20);
		$this->EnableAction("Nennsollwert");
		
		$this->RegisterVariableFloat("Reduziertsollwert", "Reduzierter-Sollwert", "IPS2Broetje.WaterTemperature", 30);
		$this->EnableAction("Reduziertsollwert");
		
		$this->RegisterVariableInteger("Freigabe", "Freigabe", "IPS2Broetje.Release", 40);
		$this->EnableAction("Freigabe");
		
		$this->RegisterVariableInteger("LegionellenFunktion", "Legionellen Funktion", "IPS2Broetje.LegionellaFunction", 50);
		$this->EnableAction("LegionellenFunktion");
		
		$this->RegisterVariableInteger("LegionellenFunktionPeriodisch", "Legionellen Funktion Periodisch", "IPS2Broetje.LegionellaPeriodically", 60);
		$this->EnableAction("LegionellenFunktionPeriodisch");
		
		$this->RegisterVariableInteger("LegionellenFunktionWochentag", "Legionellen Funktion Wochentag", "IPS2Broetje.LegionellaWeekday", 70);
		$this->EnableAction("LegionellenFunktionWochentag");
		
		$this->RegisterVariableInteger("LegionellenFunktionZeitpunkt", "Legionellen Funktion Zeitpunkt", "~UnixTimestampTime", 80);
		$this->EnableAction("LegionellenFunktionZeitpunkt");
		
		$this->RegisterVariableInteger("StatusCommand_1", "Status Legionellenfunktion Zeitpunkt", "IPS2Broetje.Status", 90);
		//$this->EnableAction("StatusCommand_1");
		
		$this->RegisterVariableFloat("Legionellenfunktionsollwert", "Legionellenfunktion-Sollwert", "IPS2Broetje.LegionellaTemperature", 100);
		$this->EnableAction("Legionellenfunktionsollwert");
		
		$this->RegisterVariableInteger("LegionellenFunktionVerweildauer", "Legionellen Funktion Verweildauer", "IPS2Broetje.Minuten", 110);
		$this->EnableAction("LegionellenFunktionVerweildauer");
		
		$this->RegisterVariableInteger("StatusCommand_2", "Status Legionellenfunktion Verweildauer", "IPS2Broetje.Status", 120);
		//$this->EnableAction("StatusCommand_2");
		
		$this->RegisterVariableFloat("Zirkulationssollwert", "Zirkulations-Sollwert", "IPS2Broetje.WaterTemperature", 130);
		$this->EnableAction("Zirkulationssollwert");
		
		$this->RegisterVariableInteger("StatusTrinkwasser", "Status Trinkwasser", "", 140);
		$this->RegisterVariableString("StatusTrinkwasserText", "Status Trinkwasser", "", 275);	
		
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
				case "Betriebsart":
					$this->SetData(10240, $Value);
				    break;
				case "Nennsollwert":
					$this->SetData(10241, ($Value * 64));
				    break;
				case "Reduziertsollwert":
					$this->SetData(10242, ($Value * 64));
				    break;
				case "Freigabe":
					$this->SetData(10243, $Value);
				    break;
				case "LegionellenFunktion":
					$this->SetData(10244, $Value);
				    break;
				case "LegionellenFunktionPeriodisch":
					$this->SetData(10245, $Value);
				    break;
				case "LegionellenFunktionWochentag":
					$this->SetData(10246, $Value);
				    break;
				case "LegionellenFunktionZeitpunkt":
					//$this->SetData(10247, $Value);
				    break;
				case "Legionellenfunktionsollwert":
					$this->SetData(10249, ($Value * 64));
				    break;
				case "LegionellenFunktionVerweildauer":
					$this->SetData(10250, $Value);
				    break;
				case "Zirkulationssollwert":
					$this->SetData(10263, ($Value * 64));
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
							If ($this->GetValue($Name) <> ($Response/$Devisor)) {
								$this->SetValue($Name, ($Response/$Devisor));
							}
							
							If ($Name == "StatusTrinkwasser") {
								$this->SetValue("StatusTrinkwasserText", $this->GetStatusCodeText($Response/$Devisor));
							}
						}
						else {
							$Minutes = ($Response/$Devisor);
							If ($this->GetValue($Name) <> mktime(0, $Minutes, 0)) {
								$this->SetValue($Name, mktime(0, $Minutes, 0));
								
								
							}
						}
					}
				}
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
			$this->GetData();
		}
	}    
	
	private function GetStatusCodeText(int $StatusCodeNumber)
	{
		$StatusCodeText = array(0 => "---", 1 => "STB angesprochen", 2 => "Störung", 3 => "Wächter angesprochen", 4 => "Handbetrieb aktiv", 
				     5 => "Schornsteinfegerfkt,Volllast", 6 => "Schornsteinfegerfkt,Teillast", 7 => "Schornsteinfegerfkt aktiv", 
				     8 => "Gesperrt, manuell", 9 => "Gesperrt, automatisch", 10 => "Gesperrt", 11 => "Anfahrentlastung", 
				     12 => "Anfahrentlastung, Teillast", 13 => "Rücklaufbegrenzung", 14 => "Rücklaufbegrenzung, Teillast", 
				     15 => "Freigegeben", 16 => "Freigegeben, Teillast", 17 => "Nachlauf aktiv", 18 => "In Betrieb", 
				     19 => "Freigegeben", 20 => "Minimalbegrenzung", 21 => "Minimalbegrenzung, Teillast", 22 => "Minimalbegrenzung aktiv", 
				     23 => "Anlagefrostschutz aktiv", 24 => "Frostschutz aktiv", 25 => "Aus", 26 => "Notbetrieb", 27 => "Gesperrt, extern", 
				     28 => "Begr Quellentemp Min", 29 => "HD bei WP-Betrieb", 30 => "Ström'wächter W'quelle", 31 => "Druckwächter W'quelle", 
				     32 => "Begr Heissgas Verdichter 1", 33 => "Begr Heissgas Verdichter 2", 34 => "Begr Ausschalttemp Max", 
				     35 => "Verd'stillstandzeit Min aktiv", 36 => "Kompensat Wärmeüberschuss", 37 => "Begrenzungszeit aktiv", 
				     38 => "Verd'laufzeit Min aktiv", 39 => "Kompensation Wärmedefizit", 40 => "Begr Spreiz Kondens Max", 
				     41 => "Begr Spreiz Kondens Min", 42 => "Begr Spreiz Verda Max", 43 => "Begr Spreiz Verda Min", 
				     44 => "Verdichter und Elektro ein", 45 => "Verdichter 1 und 2 Ein", 46 => "Verdichter 1 Ein", 47 => "Verdichter 2 Ein", 
				     48 => "Frostschutz Wärmepumpe", 49 => "Vorlauf aktiv", 50 => "Freigegeben, Verd bereit", 51 => "Keine Anforderung", 
				     52 => "Kollektorfrostschutz aktiv", 53 => "Rückkühlung aktiv", 54 => "Max Speichertemp erreicht", 
				     55 => "Verdampfungsschutz aktiv", 56 => "Überhitzschutz aktiv", 57 => "Max Ladetemp erreicht", 58 => "Ladung Trinkwasser", 
				     59 => "Ladung Pufferspeicher", 60 => "Ladung Schwimmbad", 61 => "Min Ladetemp nicht erreicht", 
				     62 => "Temp'differenz ungenügend", 63 => "Einstrahlung ungenügend", 64 => "Ladung Elektro, Notbetrieb", 
				     65 => "Ladung Elektro, Quell'schutz", 66 => "Ladung Elektroeinsatz", 67 => "Zwangsladung aktiv", 68 => "Teilladung aktiv", 
				     69 => "Ladung aktiv", 70 => "Geladen, max Speichertemp", 71 => "Geladen, max Ladetemp", 72 => "Geladen, Zwanglad Solltemp", 
				     73 => "Geladen, Solltemperatur", 74 => "Teilgeladen, Solltemperatur", 75 => "Geladen", 76 => "Kalt", 
				     77 => "Rückkühlung via Kollektor", 78 => "Rückkühlung via Erz/Hk's", 79 => "Entladeschutz aktiv", 
				     80 => "Ladezeitbegrenzung aktiv", 81 => "Ladung gesperrt", 82 => "Ladesperre aktiv", 83 => "Zwang, max Speichertemp", 
				     84 => "Zwang, max Ladetemperatur", 85 => "Zwang, Legionellensollwert", 86 => "Zwang, Nennsollwert", 
				     87 => "Ladung Elektro, Leg'sollwert", 88 => "Ladung Elektro, Nennsollwert", 89 => "Ladung Elektro, Red'sollwert", 
				     90 => "Ladung Elektro,Fros'sollwert", 91 => "Elektroeinsatz freigegeben", 92 => "Push, Legionellensollwert", 
				     93 => "Push, Nennsollwert", 94 => "Push aktiv", 95 => "Ladung, Legionellensollwert", 96 => "Ladung, Nennsollwert", 
				     97 => "Ladung, Reduziertsollwert", 98 => "Geladen, Legio'temperatur", 99 => "Geladen, Nenntemperatur", 
				     100 => "Geladen, Reduz'temperatur", 101 => "Raumfrostschutz aktiv", 102 => "Estrichfunktion aktiv", 
				     103 => "Eingeschränkt, Kesselschutz", 104 => "Eingeschränkt, TWW-Vorrang", 105 => "Eingeschränkt, Puffer", 
				     106 => "Heizbetrieb eingeschränkt", 107 => "Zwangsabnahme Puffer", 108 => "Zwangsabnahme TWW", 
				     109 => "Zwangsabnahme Erzeuger", 110 => "Zwangsabnahme", 111 => "Einschaltopt+Schnellaufheiz", 112 => "Einschaltoptimierung", 
				     113 => "Schnellaufheizung", 114 => "Heizbetrieb Komfort", 115 => "Ausschaltoptimierung", 116 => "Heizbetrieb Reduziert", 
				     117 => "Vorlauffrostschutz aktiv", 118 => "Sommerbetrieb", 119 => "Tages-Eco aktiv", 120 => "Absenkung Reduziert", 
				     121 => "Absenkung Frostschutz", 122 => "Raumtemp'begrenzung", 123 => "STB-Test aktiv", 124 => "Ladung eingeschränkt", 
				     125 => "Abtauen aktiv", 126 => "Abtropfen", 127 => "Aktiver Kühlbetrieb", 128 => "Passiver Kühlbetrieb", 
				     129 => "Abkühlen Verdampfer", 130 => "Vorwärmen für Abtauen", 131 => "Ladung Elektro, Abtauen", 132 => "Zwangsabtauen aktiv", 
				     133 => "Taupunktwächter aktiv", 134 => "Kühlgrenze TA aktiv", 135 => "Sperrdauer nach Heizen", 
				     136 => "Vorlaufsollw'anhebung Hygro", 137 => "Heizbetrieb", 138 => "Kühlbetrieb aus", 139 => "Begr Ausschalttemp. Min", 
				     140 => "Heizen Aus/Kühlen gesperrt", 141 => "Kesselfrostschutz aktiv", 142 => "Rückkühlung via TWW/Hk's", 
				     143 => "Geladen, Min Ladetemp", 144 => "Kühlbetrieb eingeschränkt", 145 => "Begr Aus'temp max Kühlen", 
				     146 => "Kühlbetrieb gesperrt", 147 => "Warm", 148 => "Kühlbetrieb bereit", 149 => "Schutzbetrieb Kühlen", 
				     150 => "Kühlbetrieb Komfort", 151 => "Lad'ng TWW+Puffer+Sch'bad", 152 => "Ladung Trinkwasser+Puffer", 
				     153 => "Ladung Trinkwasser+Sch'bad", 154 => "Ladung Puffer+Schwimmbad", 155 => "Heizbetrieb Erzeuger", 
				     156 => "Geheizt, max Schw'badtemp", 157 => "Geheizt, Sollwert Erzeuger", 158 => "Geheizt, Sollwert Solar", 
				     159 => "Geheizt", 160 => "Heizbetrieb Solar Aus", 161 => "Heizbetrieb Erzeuger Aus", 162 => "Heizbetrieb Aus", 
				     163 => "Anfeuerungshilfe aktiv", 164 => "Ladung Elektro, Zwang", 165 => "Ladung Elektro, Ersatz", 
				     166 => "In Betrieb für Heizkreis", 167 => "In Teillastbetrieb für HK", 168 => "In Betrieb für Trinkwasser", 
				     169 => "In Teillastbetrieb für TWW", 170 => "In Betrieb für HK,TWW", 171 => "In Teillastbetrieb für HK.TWW", 
				     172 => "Gesperrt, Feststoffkessel", 173 => "Freigegeben für HK,TWW", 174 => "Freigegeben für TWW", 
				     175 => "Freigegeben für HK", 176 => "Gesperrt, Aussentemperatur", 177 => "Begr Vorlauf min Taupunkt", 
				     178 => "Begr Vorlauf min TA", 179 => "Vorlaufgrenze erreicht", 180 => "Drehstrom asymmetrisch", 181 => "Niederdruck", 
				     182 => "Ventilator Überlast", 183 => "Verdichter 1 Überlast", 184 => "Verdichter 2 Überlast", 185 => "Quellenpumpe Überlast", 
				     186 => "Ström'wächter Verbraucher", 187 => "Einsatzgrenze TA Min", 188 => "Einsatzgrenze TA Max", 
				     189 => "Begr Quellentemp Min Wasser", 190 => "Begr Quellentemp Min Sole", 191 => "Begr Quellentemp Max", 
				     192 => "Zwangsabtauen Verdichter", 193 => "Zwangsabtauen Ventilator", 194 => "Abtauen mit Verdichter", 
				     195 => "Abtauen mit Ventilator", 196 => "Begr Quellentemp Min Kühlen", 197 => "Elektro Ein", 198 => "Gesperrt, Ökobetrieb", 
				     199 => "Zapfbetrieb", 200 => "Bereit", 201 => "Bereitschaftsladung", 202 => "Frostschutz Kühlen aktiv", 
				     203 => "Durchladung aktiv", 204 => "Gesperrt, Heizbetrieb", 205 => "Gesperrt, Erzeuger", 206 => "Gesperrt, Puffer", 
				     207 => "Verd'laufzeit Min aktiv, Kühl", 208 => "Verd' 1 und 2 ein, Kühlbetr", 209 => "Verdichter 1 ein,Kühlbetrieb", 
				     210 => "Verdichter 2 ein,Kühlbetrieb", 211 => "Störstellung", 212 => "Startverhinderung", 213 => "Ausserbetriebsetzung", 
				     214 => "Sicherheitszeit", 215 => "Inbetriebsetzung", 216 => "Standby", 217 => "Heimlauf", 218 => "Vorlüften", 
				     219 => "Nachlüften", 220 => "Reglerstopp aktiv", 221 => "Warmhaltebetrieb ein", 222 => "Warmhaltebetrieb aktiv", 
				     223 => "Frostschutz Durchl'erhitzer", 224 => "Zünden", 225 => "Einschwingzeit", 226 => "Exotengasbetrieb", 
				     227 => "Drifttest aktiv", 228 => "Sonderbetrieb", 229 => "Einstellbetrieb", 230 => "Exemplareinstellung aktiv", 
				     231 => "Start manueller Drifttest", 232 => "Abgastemp, Abschaltung", 233 => "Abgastemp, Leist'begrenzung", 
				     234 => "Abgastemperatur zu hoch", 235 => "Wasserdruck zu niedrig", 236 => "Partyfunktion aktiv", 
				     237 => "Umladung, Legionellensollwert", 238 => "Umladung, Nennsollwert", 239 => "Umladung, Reduziertsollwert", 
				     240 => "Umladung aktiv", 241 => "Restwärmenutzung", 242 => "Umschichtung aktiv", 243 => "Warmhaltebetrieb freigegeb'", 
				     244 => "Erzeuger freigegeben", 245 => "STB begrenzt Leistung", 246 => "Netzunterspannung", 247 => "Unterkühlschutz aktiv", 
				     248 => "Pumpendauerlauf", 249 => "Ladung opt Energie, Nenn", 250 => "Ladung opt Energie, Legio", 
				     251 => "Ladung opt Energie EW, Nenn", 252 => "Ladung opt Energie EW, Legio", 253 => "Durchfluss gering", 
				     254 => "Kältemittel abpumpen, Manuell", 255 => "Sammelzustand 255", 256 => "Kältemittel abpumpen", 
				     257 => "Startverzögerung Abtauen", 258 => "Verdichter gesperrt", 259 => "Gesperrt, Quellentemp Max", 
				     260 => "Gesperrt, Quellentemp Min", 261 => "Gesperrt, Rücklauftemp Max", 262 => "Gesperrt, Rücklauftemp Min", 
				     263 => "Gesperrt, Vorlauftemp Max", 264 => "Gesperrt, Vorlauftemp Min", 265 => "Gesperrt, Kondens'temp Max", 
				     266 => "Gesperrt, Verdamp'temp Min", 267 => "Gesperrt, Heissgastemp Max", 268 => "Begr Verdampfungstemp Min", 
				     269 => "Begr Kondensationstemp Max", 270 => "Begr Verdampfungstemp Max", 271 => "Elektroeinsatz gesperrt", 
				     272 => "Hochtemperaturladung aktiv", 273 => "Störung Sanftanlasser 1", 274 => "Störung Sanftanlasser 2", 
				     275 => "Ström'wächt Quellenzw'kreis", 276 => "Druckwächt Quellenzw'kreis", 277 => "Luftqualitätsregelung", 
				     278 => "Feuchtebegrenzung", 279 => "Lüftungsschalter", 280 => "Nachtkühlung", 281 => "Stufe 1", 282 => "Stufe 2", 
				     283 => "Stufe 3", 284 => "Stosslüften", 285 => "Kühlbetrieb Reduziert", 286 => "Anhebung Reduziert", 
				     287 => "Anhebung Schutzbetrieb", 288 => "Sperrdauer nach Kühlen", 289 => "Begr Druckdiff Proz'umkehr", 
				     290 => "Niederdruck Verdichter 2", 291 => "HD Verdicher 2 bei Betrieb", 292 => "Automatischer Betrieb", 
				     293 => "Manueller Betrieb", 294 => "Gesperrt, Leistungszahl Min", 295 => "Gesperrt, Energiepreis", 
				     296 => "Passiver Kühlbetr gesperrt", 297 => "Ström'wächter Zus'erzeuger", 298 => "Wärmerfunktion aktiv", 
				     299 => "Kälterfunktion aktiv", 300 => "Gegenwindfunktion aktiv");
		If (array_key_exists($StatusCodeNumber, $StatusCodeText)) {
			$StatusText = $StatusCodeText[$StatusCodeNumber];
		}
		else {
			$StatusText = "Unbekannter StatusCode -".$StatusCodeNumber;
		}
		
	return $StatusText;
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
}
?>
