<?php
session_start();

header('Access-Control-Allow-Origin: *');
/*
error_reporting(E_ALL);
ini_set('display_errors', 1);
*/
require_once('config.php');


$DBConnection = mysqli_connect($DBSERVER, $DBUSER, $DBPASSWORD, $DBNAME);
mysqli_select_db($DBConnection, $DBNAME);
mysqli_query($DBConnection, "SET NAMES utf8");

$ResultJSON = '{"Operation" : "NoOP", "Result" : "-100" }';
switch ($_REQUEST["Operation"]) {
    case "ListAllPatients":
        
        if((isset($_REQUEST["PatientID"]))&&(!empty($_REQUEST["PatientID"])))
        {
         $counter=0;
                $PatientQuery = 'SELECT * FROM patient WHERE PatientID='.$_REQUEST["PatientID"];
                $PatientQueryResult = mysqli_query($DBConnection, $PatientQuery);


                $ResultJSON = '{"Operation" : "ListAllPatients","Result" :[';
                while($Patientrow = mysqli_fetch_assoc($PatientQueryResult)) {
                   if($counter!=0)
                    {
                        $ResultJSON= $ResultJSON . ",";
                    }
                    $counter=$counter+1;
                    $Patient = '{"PatientID" : "' . $Patientrow["PatientID"] . '" , "Name" : "' . $Patientrow["Name"] . '" , "Surname" : "' . $Patientrow["Surname"] . '" , "BirthDate" : "' . $Patientrow["BirthDate"] .'"';



                    $PersonQuery = 'SELECT * FROM user WHERE UserID='.$_REQUEST["UserID"];
                    $PersonQueryResult = mysqli_query($DBConnection, $PersonQuery);    

                    $Personrow = mysqli_fetch_assoc($PersonQueryResult);
                    $Patient= $Patient .',"Username" : "' . $Personrow["Username"] . '" , "Password" : "' . $Personrow["Password"] . '"';
                    
                    $DoctorPatientQuery = 'SELECT * FROM doctorpatient WHERE PatientID='.$Patientrow["PatientID"];
                    $DoctorPatientQueryResult = mysqli_query($DBConnection, $DoctorPatientQuery);    
                
                    $DoctorPatientrow = mysqli_fetch_assoc($DoctorPatientQueryResult);
                    $Patient= $Patient . ' , "DoctorID" : "' . $DoctorPatientrow["DoctorID"] .'"';

                   $Patient= $Patient.'}';
                   $ResultJSON= $ResultJSON . $Patient;

                }

               $ResultJSON= $ResultJSON . ']}';
        }
        
        else{
        
            $counter=0;
            $Query = "SELECT * FROM patient";
            $QueryResult = mysqli_query($DBConnection, $Query);

            $ResultJSON = '{"Operation" : "ListAllPatients","Result" :[';
            while($row = mysqli_fetch_assoc($QueryResult)) {
               if($counter!=0)
                {
                    $ResultJSON= $ResultJSON . ",";
                }
                $counter=$counter+1;
                $result = '{"PatientID" : "' . $row["PatientID"] . '" , "UserID" : "' . $row["UserID"] .'" , "Name" : "' . $row["Name"] .'" , "Surname" : "'.$row["Surname"].'" , "BirthDate" : "'. $row["BirthDate"].'"';

                $userQuery = 'SELECT * FROM user WHERE UserID='.$row["UserID"];
                $userQueryResult = mysqli_query($DBConnection, $userQuery);    
                
                $userrow = mysqli_fetch_assoc($userQueryResult);
                $result= $result . ' , "Username" : "' . $userrow["Username"] .'" , "Password" : "' . $userrow["Password"] .'" , "UserType" : "' . $userrow["UserType"] .'"';
                
                
                $DoctorPatientQuery = 'SELECT * FROM doctorpatient WHERE PatientID='.$row["PatientID"];
                $DoctorPatientQueryResult = mysqli_query($DBConnection, $DoctorPatientQuery);    
                
                $DoctorPatientrow = mysqli_fetch_assoc($DoctorPatientQueryResult);
                $result= $result . ' , "DoctorID" : "' . $DoctorPatientrow["DoctorID"] .'"';
                
                
               $result= $result.'}';
               $ResultJSON= $ResultJSON . $result;

            }
            
           $ResultJSON= $ResultJSON . ']}';
        }    
        break;
        case "AddPatient":
        
         
            $Query = 'INSERT INTO user (Username,Password,UserType) VALUES ("'.$_REQUEST['Username'].'","'.$_REQUEST['Password'].'","Patient")';
            $QueryResult = mysqli_query($DBConnection, $Query);
            if($QueryResult==True)
            {
                $last_id = mysqli_insert_id($DBConnection);
                $ResultJSON = '{"Operation" : "AddPatient", "Result" : "1"}';
            }
        
            $CAddressQuery = 'INSERT INTO patient (UserID,Name,Surname,BirthDate) VALUES ("'.$last_id.'","'.$_REQUEST['Name'].'","'.$_REQUEST['Surname'].'","'.$_REQUEST['BirthDate'].'")';
            $CAddressQueryResult = mysqli_query($DBConnection, $CAddressQuery);
        
            $last_id = mysqli_insert_id($DBConnection);
            
            
            $CAddressQuery = 'INSERT INTO doctorpatient (DoctorID,PatientID) VALUES ("'.$_REQUEST['DoctorID'].'","'.$last_id.'")';
            $CAddressQueryResult = mysqli_query($DBConnection, $CAddressQuery);
        
            $Query = 'INSERT INTO standardrates (PatientID) VALUES ("'.$last_id.'")';
            $QueryResult = mysqli_query($DBConnection, $Query);
            
        break; 
        
        case "EditPatient":
        
            $Query = 'UPDATE patient SET Name="'.$_REQUEST['Name'].'",Surname="'.$_REQUEST['Surname'].'",BirthDate="'.$_REQUEST['BirthDate'].'" WHERE PatientID="'.$_REQUEST['PatientID'].'"';
            $QueryResult = mysqli_query($DBConnection, $Query);
        
            $Query = 'UPDATE user SET Username="'.$_REQUEST['Username'].'",Password="'.$_REQUEST['Password'].'" WHERE UserID="'.$_REQUEST['UserID'].'"';
            $QueryResult = mysqli_query($DBConnection, $Query);
        
            
            $Query = 'UPDATE doctorpatient SET DoctorID="'.$_REQUEST['DoctorID'].'" WHERE PatientID="'.$_REQUEST['PatientID'].'"';
            $QueryResult = mysqli_query($DBConnection, $Query);
        
            if($QueryResult==True)
            {
                $ResultJSON = '{"Operation" : "EditPatient", "Result" : "1"}';
            }
        break;
        
         case "DeletePatient":
        
            $Query = 'DELETE FROM patient WHERE PatientID="'.$_REQUEST['PatientID'].'"';
            $QueryResult = mysqli_query($DBConnection, $Query);
            
            $Query = 'DELETE FROM user WHERE UserID="'.$_REQUEST['UserID'].'"';
            $QueryResult = mysqli_query($DBConnection, $Query);
        
            $Query = 'DELETE FROM doctorpatient WHERE PatientID="'.$_REQUEST['PatientID'].'"';
            $QueryResult = mysqli_query($DBConnection, $Query);
        
            if($QueryResult==True)
            {
                $ResultJSON = '{"Operation" : "DeleteJob", "Result" : "1"}';
            }
        
        break;
       
        case "ListAllDoctors":
            if((isset($_REQUEST["DoctorID"]))&&(!empty($_REQUEST["DoctorID"])))
            {
                $counter=0;
                $DoctorQuery = 'SELECT * FROM doctor WHERE DoctorID='.$_REQUEST["DoctorID"];
                $DoctorQueryResult = mysqli_query($DBConnection, $DoctorQuery);


                $ResultJSON = '{"Operation" : "ListAllDoctors","Result" :[';
                while($Doctorrow = mysqli_fetch_assoc($DoctorQueryResult)) {
                   if($counter!=0)
                    {
                        $ResultJSON= $ResultJSON . ",";
                    }
                    $counter=$counter+1;
                    $Doctor = '{"DoctorID" : "' . $Doctorrow["DoctorID"] . '" , "UserID" : "' . $Doctorrow["UserID"] . '" , "Name" : "' . $Doctorrow["Name"] . '" , "Surname" : "' . $Doctorrow["Surname"] . '" , "BirthDate" : "' . $Doctorrow["BirthDate"] . '" , "Specialization" : "' . $Doctorrow["Specialization"] .'"';



                    $PersonQuery = 'SELECT * FROM user WHERE UserID='.$Doctorrow["UserID"];
                    $PersonQueryResult = mysqli_query($DBConnection, $PersonQuery);    

                    $Personrow = mysqli_fetch_assoc($PersonQueryResult);
                    $Doctor= $Doctor .',"Username" : "' . $Personrow["Username"] . '" , "Password" : "' . $Personrow["Password"] .'"';


                   $Doctor= $Doctor.'}';
                   $ResultJSON= $ResultJSON . $Doctor;

                }

               $ResultJSON= $ResultJSON . ']}';
            }
            else{
                $counter=0;
                $DoctorQuery = 'SELECT * FROM doctor';
                $DoctorQueryResult = mysqli_query($DBConnection, $DoctorQuery);


                $ResultJSON = '{"Operation" : "ListAllDoctors","Result" :[';
                while($Doctorrow = mysqli_fetch_assoc($DoctorQueryResult)) {
                   if($counter!=0)
                    {
                        $ResultJSON= $ResultJSON . ",";
                    }
                    $counter=$counter+1;
                    $Doctor = '{"DoctorID" : "' . $Doctorrow["DoctorID"] . '" , "UserID" : "' . $Doctorrow["UserID"] . '" , "Name" : "' . $Doctorrow["Name"] . '" , "Surname" : "' . $Doctorrow["Surname"] . '" , "Specialization" : "' . $Doctorrow["Specialization"] . '" , "BirthDate" : "' . $Doctorrow["BirthDate"] .'"';



                    $PersonQuery = 'SELECT * FROM user WHERE UserID='.$Doctorrow["UserID"];
                    $PersonQueryResult = mysqli_query($DBConnection, $PersonQuery);    

                    $Personrow = mysqli_fetch_assoc($PersonQueryResult);
                    $Doctor= $Doctor .',"Username" : "' . $Personrow["Username"] . '" , "Password" : "' . $Personrow["Password"] .'"';


                   $Doctor= $Doctor.'}';
                   $ResultJSON= $ResultJSON . $Doctor;

                }

               $ResultJSON= $ResultJSON . ']}';
            }
        break;
        case "AddDoctor":
        
         
            $Query = 'INSERT INTO user (Username,Password,UserType) VALUES ("'.$_REQUEST['Username'].'","'.$_REQUEST['Password'].'","Doctor")';
            $QueryResult = mysqli_query($DBConnection, $Query);
            if($QueryResult==True)
            {
                $last_id = mysqli_insert_id($DBConnection);
                $ResultJSON = '{"Operation" : "AddDoctor", "Result" : "1"}';
            }
        
            $DoctorQuery = 'INSERT INTO Doctor (UserID,Name,Surname,BirthDate,Specialization) VALUES ("'.$last_id.'","'.$_REQUEST['Name'].'","'.$_REQUEST['Surname'].'","'.$_REQUEST['BirthDate'].'","'.$_REQUEST['Specialization'].'")';
            $DoctorQueryResult = mysqli_query($DBConnection, $DoctorQuery);
        
            $last_id = mysqli_insert_id($DBConnection);
            
            
            
        break; 
        
        case "EditDoctor":
        
            $Query = 'UPDATE doctor SET Name="'.$_REQUEST['Name'].'",Surname="'.$_REQUEST['Surname'].'",BirthDate="'.$_REQUEST['BirthDate'].'",Specialization="'.$_REQUEST['Specialization'].'" WHERE DoctorID="'.$_REQUEST['DoctorID'].'"';
            $QueryResult = mysqli_query($DBConnection, $Query);
        
            $Query = 'UPDATE user SET Username="'.$_REQUEST['Username'].'",Password="'.$_REQUEST['Password'].'" WHERE UserID="'.$_REQUEST['UserID'].'"';
            $QueryResult = mysqli_query($DBConnection, $Query);
        
        
            if($QueryResult==True)
            {
                $ResultJSON = '{"Operation" : "EditDoctor", "Result" : "1"}';
            }
        break;
        
         case "DeleteDoctor":
        
            $Query = 'DELETE FROM doctor WHERE DoctorID="'.$_REQUEST['DoctorID'].'"';
            $QueryResult = mysqli_query($DBConnection, $Query);
            
            $Query = 'DELETE FROM user WHERE UserID="'.$_REQUEST['UserID'].'"';
            $QueryResult = mysqli_query($DBConnection, $Query);
        
            if($QueryResult==True)
            {
                $ResultJSON = '{"Operation" : "DeleteDoctor", "Result" : "1"}';
            }
        
        break;
        case "ListAllSensorReadings":
            if((isset($_REQUEST["PatientID"]))&&(!empty($_REQUEST["PatientID"])))
            {
                $counter=0;
                $ReadingQuery = 'SELECT * FROM sensorreading WHERE PatientID='.$_REQUEST['PatientID'].' ORDER BY ReadingTime DESC';
                $ReadingQueryResult = mysqli_query($DBConnection, $ReadingQuery);


                $ResultJSON = '{"Operation" : "ListAllSensorReadings","Result" :[';
                while($Readingrow = mysqli_fetch_assoc($ReadingQueryResult)) {
                   if($counter!=0)
                    {
                        $ResultJSON= $ResultJSON . ",";
                    }
                    $counter=$counter+1;
                    $Reading = '{"SensorReadingID" : "' . $Readingrow["SensorReadingID"] . '" , "HeartRate" : "' . $Readingrow["HeartRate"] . '" , "Temperature" : "' . $Readingrow["Temperature"] . '" , "SPO2" : "' . $Readingrow["SPO2"] . '" , "ReadingTime" : "' . $Readingrow["ReadingTime"] .'"';



                   $Reading= $Reading.'}';
                   $ResultJSON= $ResultJSON . $Reading;

                }

               $ResultJSON= $ResultJSON . ']}';
            }
        else{
                $PatientQuery = 'SELECT * FROM patient WHERE UserID='.$_SESSION["UserID"];
                $PatientQueryResult = mysqli_query($DBConnection, $PatientQuery);
                $Patientrow = mysqli_fetch_assoc($PatientQueryResult);
                
                
                
                $counter=0;
                $ReadingQuery = 'SELECT * FROM sensorreading WHERE PatientID='.$Patientrow['PatientID'].' ORDER BY ReadingTime DESC';
                $ReadingQueryResult = mysqli_query($DBConnection, $ReadingQuery);


                $ResultJSON = '{"Operation" : "ListAllSensorReadings","Result" :[';
                while($Readingrow = mysqli_fetch_assoc($ReadingQueryResult)) {
                   if($counter!=0)
                    {
                        $ResultJSON= $ResultJSON . ",";
                    }
                    $counter=$counter+1;
                    $Reading = '{"SensorReadingID" : "' . $Readingrow["SensorReadingID"] . '" , "HeartRate" : "' . $Readingrow["HeartRate"] . '" , "Temperature" : "' . $Readingrow["Temperature"] . '" , "SPO2" : "' . $Readingrow["SPO2"] . '" , "ReadingTime" : "' . $Readingrow["ReadingTime"] .'"';



                   $Reading= $Reading.'}';
                   $ResultJSON= $ResultJSON . $Reading;

                }

               $ResultJSON= $ResultJSON . ']}';
            
        }
        break;
        case "ListAllDoctorPatients":
                $DoctorQuery = 'SELECT * FROM doctor WHERE UserID='.$_SESSION["UserID"];
                $DoctorQueryResult = mysqli_query($DBConnection, $DoctorQuery);
                $Doctorrow = mysqli_fetch_assoc($DoctorQueryResult);
                
                
                
                $counter=0;
                $DoctorPatientQuery = 'SELECT * FROM doctorpatient WHERE DoctorID='.$Doctorrow['DoctorID'];
                $DoctorPatientQueryResult = mysqli_query($DBConnection, $DoctorPatientQuery);


                $ResultJSON = '{"Operation" : "ListAllDoctorPatients","Result" :[';
                while($DoctorPatientrow = mysqli_fetch_assoc($DoctorPatientQueryResult)) {
                   if($counter!=0)
                    {
                        $ResultJSON= $ResultJSON . ",";
                    }
                    $counter=$counter+1;
                    
    
                    $PatientQuery = 'SELECT * FROM patient WHERE PatientID='.$DoctorPatientrow['PatientID'];
                    $PatientQueryResult = mysqli_query($DBConnection, $PatientQuery);
                    $Patientrow = mysqli_fetch_assoc($PatientQueryResult);
                    
                    
                    $Patient = '{"Name" : "' . $Patientrow["Name"] . '" , "Surname" : "' . $Patientrow["Surname"] .'" , "PatientID" : "' . $Patientrow["PatientID"] . '" , "BirthDate" : "' . $Patientrow["BirthDate"] . '" , "CurrentDate" : "' . date("Y-m-d H:i:s").'"';



                   $Patient= $Patient.'}';
                   $ResultJSON= $ResultJSON . $Patient;

                }

               $ResultJSON= $ResultJSON . ']}';
        break;
        case "ListAllStandardRates":
            if((isset($_REQUEST["PatientID"]))&&(!empty($_REQUEST["PatientID"])))
            {
                $counter=0;
                $ReadingQuery = 'SELECT * FROM standardrates WHERE PatientID='.$_REQUEST['PatientID'];
                $ReadingQueryResult = mysqli_query($DBConnection, $ReadingQuery);


                $ResultJSON = '{"Operation" : "ListAllStandardRates","Result" :[';
                while($Readingrow = mysqli_fetch_assoc($ReadingQueryResult)) {
                   if($counter!=0)
                    {
                        $ResultJSON= $ResultJSON . ",";
                    }
                    $counter=$counter+1;
                    $Reading = '{"StandardRatesID" : "' . $Readingrow["StandardRatesID"] . '" , "PatientID" : "' . $Readingrow["PatientID"] . '" , "LowHeartRate" : "' . $Readingrow["LowHeartRate"] . '" , "HighHeartRate" : "' . $Readingrow["HighHeartRate"] . '" , "LowTemperature" : "' . $Readingrow["LowTemperature"] .'" , "HighTemperature" : "' . $Readingrow["HighTemperature"] .'" , "LowSPO2" : "' . $Readingrow["LowSPO2"] .'" , "HighSPO2" : "' . $Readingrow["HighSPO2"] .'" , "SettingTime" : "' . $Readingrow["SettingTime"] .'"';



                   $Reading= $Reading.'}';
                   $ResultJSON= $ResultJSON . $Reading;

                }

               $ResultJSON= $ResultJSON . ']}';
            }
        break;
        case "EditStandardRates":
        
            $Query = 'UPDATE standardrates SET LowHeartRate="'.$_REQUEST['LowHeartRate'].'",HighHeartRate="'.$_REQUEST['HighHeartRate'].'",LowTemperature="'.$_REQUEST['LowTemperature'].'",HighTemperature="'.$_REQUEST['HighTemperature'].'",LowSPO2="'.$_REQUEST['LowSPO2'].'",HighSPO2="'.$_REQUEST['HighSPO2'].'",SettingTime="'.date("Y-m-d H:i:s").'" WHERE PatientID="'.$_REQUEST['PatientID'].'"';
            $QueryResult = mysqli_query($DBConnection, $Query);
        
            if($QueryResult==True)
            {
                $ResultJSON = '{"Operation" : "EditStandardRates", "Result" : "1"}';
            }
        break;
         case "ListAllSensorReadingsForChart":
            if((isset($_REQUEST["PatientID"]))&&(!empty($_REQUEST["PatientID"])))
            {
                $counter=0;
                $ReadingQuery = 'SELECT * FROM sensorreading WHERE PatientID='.$_REQUEST['PatientID'].' ORDER BY ReadingTime DESC';
                $ReadingQueryResult = mysqli_query($DBConnection, $ReadingQuery);


                $ResultJSON = '{"Operation" : "ListAllSensorReadings","Result" :[';
                while($Readingrow = mysqli_fetch_assoc($ReadingQueryResult)) {
                   if($counter!=0)
                    {
                        $ResultJSON= $ResultJSON . ",";
                    }
                    $counter=$counter+1;
                    $Reading = '{"SensorReadingID" : "' . $Readingrow["SensorReadingID"] . '" , "HeartRate" : "' . $Readingrow["HeartRate"] . '" , "Temperature" : "' . $Readingrow["Temperature"] . '" , "SPO2" : "' . $Readingrow["SPO2"] . '" , "ReadingTime" : "' . $Readingrow["ReadingTime"] .'"';



                   $Reading= $Reading.'}';
                   $ResultJSON= $ResultJSON . $Reading;

                }

               $ResultJSON= $ResultJSON . ']}';
            }
        else{
                $PatientQuery = 'SELECT * FROM patient WHERE UserID='.$_SESSION["UserID"];
                $PatientQueryResult = mysqli_query($DBConnection, $PatientQuery);
                $Patientrow = mysqli_fetch_assoc($PatientQueryResult);
                
                
                
                $counter=0;
                $ReadingQuery = 'SELECT * FROM sensorreading WHERE PatientID='.$Patientrow['PatientID'].' ORDER BY ReadingTime DESC';
                $ReadingQueryResult = mysqli_query($DBConnection, $ReadingQuery);


                $ResultJSON = '[';
                while($Readingrow = mysqli_fetch_assoc($ReadingQueryResult)) {
                   if($counter!=0)
                    {
                        $ResultJSON= $ResultJSON . ",";
                    }
                    $counter=$counter+1;
                    $Reading = '{"SensorReadingID" : "' . $Readingrow["SensorReadingID"] . '" , "HeartRate" : "' . $Readingrow["HeartRate"] . '" , "Temperature" : "' . $Readingrow["Temperature"] . '" , "SPO2" : "' . $Readingrow["SPO2"] . '" , "ReadingTime" : "' . $Readingrow["ReadingTime"] .'"';



                   $Reading= $Reading.'}';
                   $ResultJSON= $ResultJSON . $Reading;

                }

               $ResultJSON= $ResultJSON . ']';
            
        }
        break;
}

echo $ResultJSON;
?>