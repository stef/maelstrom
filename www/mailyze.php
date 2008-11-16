<?php

$MAILBOXOWNER="Marsiske Stefan";
$dburl='sqlite:'.$_SERVER['DOCUMENT_ROOT'].'/mailyze/db/messages.db';

// get all mails from person

function contactMails($db) {
   global $MAILBOXOWNER;
   list($start, $time)=mailTimeFrame($db);

   if(isset($_GET['start'])) {
      $start=$_GET['start'];
   }
   if(isset($_GET['end'])) {
      $end=$_GET['end'];
   }
   if(isset($_GET['c'])) {
      $person=$_GET['c'];
   } else {
      $person=$MAILBOXOWNER;
   }

   $q="select count(message.id) as count,
              date(message.delivered) as delivered
         from message, role, email, person 
         where message.id=role.msg_id and 
               role.email_id=email.id and
               email.owner_id==person.id and
               person.fullname=='$person'
         group by delivered
         order by delivered"; //and
               //date(message.delivered)>='$start' AND
               //date(message.delivered)<'$end'";
   // TODO add window handling if needed

   foreach ($db->query($q) as $row) {
      $results[]=array('date' => $row['delivered'],
                       'count' => $row['count']);
   }
   return ($results);
}

// get all contacts with weights for person

function secondContacts($db) {
   list($start, $time)=mailTimeFrame($db);

   if(isset($_GET['start'])) {
      $start=$_GET['start'];
   }
   if(isset($_GET['end'])) {
      $end=$_GET['end'];
   }
   if(isset($_GET['c'])) {
      $person=$_GET['c'];
   } else {
      $person=$MAILBOXOWNER;
   }

   // TODO add window handling if needed, should be enough if the subquery 
   // return messages in the interval
   //and date(message.delivered)>='$start' AND
   //date(message.delivered)<'$end'";
   $q="select person.fullname as contact,
              count(person.id) as count,
              max(date(message.delivered)) as date
         from message, role, email, person
         where message.id in (select message.id 
                             from message, role, email, person 
                             where message.id==role.msg_id and 
                                   role.email_id==email.id and
                                   email.owner_id==person.id and
                                   person.fullname like '$person') and
              message.id==role.msg_id and
              role.email_id==email.id and
              email.owner_id==person.id and 
              person.fullname not like '$person'
         group by contact;";
   foreach ($db->query($q) as $row) {
      $results[]=array($row['contact'],$row['count'],$row['lastseen']);
   }
   return ($results);
}

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
   list($start, $end)=mailTimeFrame($db);
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
   list($start, $end)=mailTimeFrame($db);

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
                       'date' => $row['delivered']);
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
   elseif($_GET['op']=="contactMails") {
      //header("Content-type: text/plain");
      print json_encode(contactMails($db));
   }
   elseif($_GET['op']=="secondContacts") {
      //header("Content-type: text/plain");
      print json_encode(secondContacts($db));
   }
}
?>
