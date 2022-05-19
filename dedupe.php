<?php

$dedupe = new DeDupe('C:\contacts\old.csv', 'C:\contacts\new.csv');

class DeDupe {
    
    private $inputCSVname = false;
    private $outputCSVname = false;
    
    public function __construct($inputCSVname, $outputCSVname){
        if (!file_exists($inputCSVname)){
            throw new Exception ('File does not exist at: '.$inputCSVname);
        }
        $this->inputCSVname = $inputCSVname;
        $this->outputCSVname = $outputCSVname;
        $this->createOutput();
    } 
    private function createOutput(){
        $handle = fopen($this->inputCSVname, "r");
        $c = 0;//Counter for the current row
        $columns = array();
        $output=array();
        $notes=0;
        $first=0;
        $last=0;
        $bphone=0;
        $hphone=0;
        $mphone=0;
        $pphone=0;
        $email=0;
        while (($data = fgetcsv($handle, 100000, ",")) !== FALSE) {
            $c++;
             if ($c == 1){//Put the schema/fields/columns into a reference array
                foreach ($data as $key => $value){
                    $columns[$key]=$value;
                    //Find Array location of critical fields
                    if ($value=='Notes') $notes = $key;
                    if ($value=='First Name') $first = $key;
                    if ($value=='Last Name') $last = $key;
                    if ($value=='Business Phone') $bphone = $key;
                    if ($value=='Home Phone') $hphone = $key;
                    if ($value=='Mobile Phone') $mphone = $key;
                    if ($value=='Primary Phone') $pphone = $key;
                    if ($value=='E-mail Address') $email = $key;
                }
                $output[0]=$data;//Output the header into the new array
            } else {
                if (strlen($data[$last]) < 3) continue;//Don't combine short names
                if (strlen($data[$first]) < 3) continue;//Don't combine short names
                $uniqId = strtolower($data[$first]).strtolower($data[$last]);//This assumes 1 is first name and 2 is last name
                if (strlen($uniqId) < 5) continue;//Don't import short / empty people names
                if (!array_key_exists($uniqId,$output)) {
                    $output[$uniqId]=$data;//Insert the entire row
                } else {
                    foreach ($output[$uniqId] as $i => $v){
                        if ($output[$uniqId][$i] != $data[$i]){//If the new/duplicate row has a longer value in the field, update the existing value
                            if (strlen($v) < strlen($data[$i])) $output[$uniqId][$i] = $data[$i];
                        }
                    }
                }
                //Update Settings
                $output[$uniqId][$notes]='';//Unset Notes
                //Remove duplicate phone numbers
                if ($output[$uniqId][$bphone] == $output[$uniqId][$mphone]) $output[$uniqId][$bphone]='';
                if ($output[$uniqId][$hphone] == $output[$uniqId][$mphone]) $output[$uniqId][$hphone]='';
                if ($output[$uniqId][$bphone] == $output[$uniqId][$pphone]) $output[$uniqId][$bphone]='';
                if ($output[$uniqId][$hphone] == $output[$uniqId][$pphone]) $output[$uniqId][$hphone]='';
                if ($output[$uniqId][$pphone] == $output[$uniqId][$mphone]) $output[$uniqId][$pphone]='';
            }
            
        }
        fclose($handle);
        $this->writeNew($output);
        $old_rows = $c - 1;
        $new_rows = count($output) - 1;
        echo "Export complete. ".$old_rows." contacts imported. Merged and de-duplicated into ".$new_rows." \n";
    }
    private function writeNew($data){
        if (file_exists($this->outputCSVname)) unlink($this->outputCSVname);
        $out = fopen($this->outputCSVname, "w");
        foreach ($data as $row){
            fputcsv($out, $row);
        }
        fclose($out);
    }
 
}
?>