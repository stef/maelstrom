<?php

$MAILBOXOWNER="Marsiske Stefan";
$dburl='sqlite:'.$_SERVER['DOCUMENT_ROOT'].'/mailyze/db/messages.db';

function mailTimeFrame($db) {
   $q='SELECT max(delivered) AS max, min(delivered) as min FROM message';
   $result=$db->query($q)->fetch();
   $start=split(" ",$result['min']);
   $start=$start[0];
   $end=split(" ",$result['max']);
   $end=$end[0];
   return array($start,$end);
}

function contactTimeCloud($db) {
   list($start, $time)=mailTimeFrame($db);
   global $MAILBOXOWNER;

   if(isset($_GET['start'])) {
      $start=$_GET['start'];
   }
   if(isset($_GET['end'])) {
      $end=$_GET['end'];
   }

   $q="SELECT person.fullname as contact,
               count(message.id) as count,
               date(message.delivered) as date
        FROM person, email, role, message
        WHERE role.email_id==email.id AND 
              email.owner_id==person.id AND
              role.msg_id==message.id AND
              person.fullname!='$MAILBOXOWNER' AND
              date(message.delivered)>='$start' AND
              date(message.delivered)<'$end'
              GROUP BY date(delivered), person.fullname
              ORDER BY date(delivered)";
   $results=array();
   $curdate="00-00-00";
   foreach ($db->query($q) as $row) {
      //print_r($row);
      //print "<br>";
      $date=$row['date'];
      if($date>$curdate) {
         // row is a new day
         if(isset($res)) {
            // store the list of contact volumes in result vector
            $results[]=array($curdate, $res);
         }
         // create a new list of contact volumes
         $res=array(array($row['contact'], $row['count']));
         // set curdate to the currently created new day
         $curdate=$date;
      } elseif($date==$curdate) {
         // store the contact in the current days volume list
         $res[]=array($row['contact'], $row['count']);
      } else {
         print "curdate $curdate";
         print "date $date";
         print_r($row);
      }
   }
   if($res) {
      $results[]=array($curdate, $res);
   }
   return ($results);
}

function mailFrequency($db) {
   list($start, $time)=mailTimeFrame($db);

   if(isset($_GET['start'])) {
      $start=$_GET['start'];
   }
   if(isset($_GET['end'])) {
      $end=$_GET['end'];
   }

   $q="SELECT date(delivered) as delivered,
             count(id) as count
        FROM message
        WHERE delivered>='$start' AND
              delivered<'$end'
        GROUP BY date(delivered)
        ORDER BY date(delivered)";

   foreach ($db->query($q) as $row) {
      $results[]=array('count' => $row['count'],
                       'delivered' => $row['delivered']);
   }
   return ($results);
}

if(isset($_GET['op'])) {
   try {
      $db= new PDO($dburl);
   }
   catch( PDOException $exception ){
      die($exception->getMessage());
   }

   if($_GET['op']=="contactTimeCloud") { 
      //header("Content-type: text/plain");
      print json_encode(contactTimeCloud($db));
   } 
   elseif($_GET['op']=="mailFrequency") {
      //header("Content-type: text/plain");
      print json_encode(mailFrequency($db));
   }
}
?>
