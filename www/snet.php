<?php
/* Maelstrom - visualizing email contacts
   Copyright© 2008-2009 Stefan Marsiske <my name at gmail.com> */

$dburl='sqlite:'.$_SERVER['DOCUMENT_ROOT'].'/maelstrom/db/messages.db';

// get all mails from person

function mailTimeFrame($db) {
   $q='SELECT max(delivered) AS max, min(delivered) as min FROM message';
   $result=$db->query($q)->fetch();
   $start=split(" ",$result['min']);
   $start=$start[0];
   $end=split(" ",$result['max']);
   $end=$end[0];
   return array($start,$end);
}

function makeNode($name/*, $weight=Array()*/) {
  return Array("id" => $name
               ,"name" => $name
               ,"children" => array()
               ,"data" => array(array("key" => "weight", "value" => 0))
               );
}

function makeTree($db,$name,$level=2/*,$weight=Array()*/) {
  $node=makeNode($name/*,$weight*/);
  if($level>0) {
    $cl=getContacts($db,$name);
    foreach($cl as $c) {
      $node["children"][]=makeTree($db,$c["name"],$level-1/*,$c["weight"]*/);
    }
  }
  return $node;
}

function getContacts($db,$person) {
   list($start, $end)=mailTimeFrame($db);

   if(isset($_GET['start'])) {
      $start=$_GET['start'];
   }
   if(isset($_GET['end'])) {
      $end=$_GET['end'];
   }

   // TODO add window handling if needed, should be enough if the subquery
   // return messages in the interval
   //and date(message.delivered)>='$start' AND
   //date(message.delivered)<'$end'";
   $q="select distinct person.fullname as contact
         from  role, email, person
         where role.msg_id in (select message.id
                             from message, role, email, person
                             where message.id==role.msg_id and
                                   role.email_id==email.id and
                                   email.owner_id==person.id and
                                   person.fullname like '$person' and
                                   date(message.delivered)>='$start' AND
                                   date(message.delivered)<'$end') and
              role.email_id==email.id and
              email.owner_id==person.id and
              person.fullname not like '$person';";
   $results=array();
   $r=$db->query($q);
   foreach ($r as $row) {
      $results[]=Array("name" => $row['contact']
                       /*,"weight" => $row['weight']*/);
   }
   return ($results);
}

try {
  $db= new PDO($dburl);
}
catch( PDOException $exception ){
  die($exception->getMessage());
}

if(!isset($_GET['c'])) {
  die;
 }
$c=$_GET['c'];

//header("Content-type: text/plain");
print json_encode(makeTree($db,$c));
?>
