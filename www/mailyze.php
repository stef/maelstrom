<?php

$dburl='sqlite:'.$_SERVER['DOCUMENT_ROOT'].'/mailyze/trunk/db/messages.db';

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

   if(isset($_GET['start'])) {
      $start=$_GET['start'];
   }
   if(isset($_GET['end'])) {
      $end=$_GET['end'];
   }

   $q="SELECT person.fullname as contact,
               count(person.id) as count,
               max(delivered) as lastseen
        FROM person, email, role, message
        WHERE role.email_id==email.id AND 
              email.owner_id==person.id AND
              role.msg_id==message.id AND
              message.delivered>='$start' AND
              message.delivered<'$end'
        GROUP BY person.fullname
        ORDER BY contact";

   foreach ($db->query($q) as $row) {
      $date=$date[0];
      $results[]=(array('name'=> $row['contact'],
         'count'=> $row['count'],
         'lastseen'=> $row['lastseen']));
   }
   print json_encode($results);
}

function mailFrequency($db) {
}

if(isset($_GET['op'])) {
   try {
      $db= new PDO($dburl);
   }
   catch( PDOException $exception ){
      die($exception->getMessage());
   }

   if($_GET['op']=="contactTimeCloud") { 
      print json_encode(contactTimeCloud($db));
   } 
   elseif($_GET['op']=="mailFrequency") {
      print json_encode(mailFrequency($db));
   }
}
?>
