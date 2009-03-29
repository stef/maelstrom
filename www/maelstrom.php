<?php
/* Maelstrom - visualizing email contacts
   Copyright© 2008-2009 Stefan Marsiske <my name at gmail.com> */

$MAILBOXOWNER=""; //IMPORTANT CONFIGURE THESE!!!!
$dburl='sqlite:'.$_SERVER['DOCUMENT_ROOT'].'/maelstrom/db/messages.db';
try {
  $db= new PDO($dburl);
}
catch( PDOException $exception ){
  die($exception->getMessage());
}

list($start, $end)=mailTimeFrame($db);
if(isset($_GET['start'])) {
  $start=$_GET['start'];
}
if(isset($_GET['end'])) {
  $end=$_GET['end'];
}

// get all mails from person

function contactMails($db) {
   global $MAILBOXOWNER;
   global $start, $end;

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
   global $start, $end;
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
              date(message.delivered) as date
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
         group by contact
         order by date;";
   $results=array();
   $curdate="00-00-00";
   foreach ($db->query($q) as $row) {
      //$results[]=array($row['contact'],$row['count'],$row['date']);
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
   // append the last day
   if($res) {
      $results[]=array($curdate, $res);
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
   global $start, $end;
   global $MAILBOXOWNER;

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
   // append the last day
   if($res) {
      $results[]=array($curdate, $res);
   }
   return ($results);
}

function contactOrgs($db) {
   global $start, $end;

   if(isset($_GET['c'])) {
      $user=$_GET['c'];
   } else {
      $user=$MAILBOXOWNER;
   }

   $q="select mailserver as org,
             count(role.id) as count,
             date(message.delivered) as date
        from email, person, role, message
        where role.email_id==email.id and
             email.owner_id==person.id and
             role.msg_id==message.id and
             fullname='$user'
        group by org, delivered
        order by delivered;";
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
         $res=array(array($row['org'], $row['count']));
         // set curdate to the currently created new day
         $curdate=$date;
      } elseif($date==$curdate) {
         // store the contact in the current days volume list
         $res[]=array($row['org'], $row['count']);
      } else {
         header("Content-type: text/plain");
         print "curdate $curdate";
         print "date $date";
         print_r($row);
      }
   }
   // append the last day
   if($res) {
      $results[]=array($curdate, $res);
   }
   return ($results);
}

function orgContacts($db) {
   global $start, $end;

   if(!isset($_GET['o'])) {
     die;
   }
   $org=$_GET['o'];
   $q="select person.fullname as contact,
              count(person.id) as count,
              date(message.delivered) as date
         from message, role, email, person
         where mailserver like '$org' and
              message.id==role.msg_id and
              role.email_id==email.id and
              email.owner_id==person.id
         group by contact
         order by date;";
   $results=array();
   $curdate="00-00-00";
   foreach ($db->query($q) as $row) {
      //$results[]=array($row['contact'],$row['count'],$row['date']);
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
   // append the last day
   if($res) {
      $results[]=array($curdate, $res);
   }
   return ($results);
}

function mailFrequency($db) {
   global $start, $end;

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

function getEdgeFrequency($db) {
  global $_GET;
  global $start, $end;
  if(isset($_GET['c1'])) {
    $c1=$_GET['c1'];
  } else {
    die;
  }
  if(isset($_GET['c2'])) {
    $c2=$_GET['c2'];
  } else {
    die;
  }
  $q="SELECT date(delivered) as delivered,
             count(message.id) as count
        FROM message,
             email as se,
             person as sp,
             role,
             email as re,
             person as rp
        WHERE ((sp.fullname==:c1 and rp.fullname==:c2) or
               (sp.fullname==:c2 and rp.fullname==:c1 )) and
              date(delivered)>=:start AND
              date(delivered)<:end and

              se.id==message.sender_id and
              sp.id==se.owner_id and
              role.msg_id==message.id and
              re.id==role.email_id and
              rp.id==re.owner_id
        GROUP BY date(delivered)
        ORDER BY date(delivered)";

  $query = $db->prepare($q);
  $query->execute(array(":c1" => $c1,
                        ":c2" => $c2,
                        ":start" => $start,
                        ":end" => $end));
  for($i=0; $row = $query->fetch(); $i++){
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
   elseif($_GET['op']=="contactOrgs") {
     //header("Content-type: text/plain");
     print json_encode(contactOrgs($db));
   }
   elseif($_GET['op']=="orgContacts") {
     //header("Content-type: text/plain");
     print json_encode(orgContacts($db));
   }
   elseif($_GET['op']=="getEdgeFrequency") {
      //header("Content-type: text/plain");
     print json_encode(getEdgeFrequency($db)); // careful! does not take $db as a param!!!! new approach, introduces code inconsistency. :(
   }
}

function getEdges() {
  global $db,$_GET;
  global $start, $end;
  if(isset($_GET['c'])) {
    $c=$_GET['c'];
  } else {
    die;
  }

  $q="select person.fullname as contact,
             count(person.fullname) as weight
       from person as p,
            email,
            message,
            role,
            email as rec,
            person
       where p.fullname==:c and
             date(delivered)>=:start AND
             date(delivered)<:end and
             p.id==rec.owner_id and
             rec.id==role.email_id and
             role.msg_id==message.id and
             email.id==message.sender_id and
             person.id==email.owner_id
       group by contact
       having count(person.fullname)>1
       order by weight desc, contact;";
  $cache=array();
  $query = $db->prepare($q);
  $query->execute(array(":c" => $c,
                        ":start" => $start,
                        ":end" => $end));
  for($i=0; $row = $query->fetch(); $i++){
    if(array_key_exists($row['contact'],$cache)){
      $cache[$row['contact']]=array('from' => $row['weight']);
      $cache['total']+=$row['weight'];
    } else {
      $cache[$row['contact']]=array('from'=>$row['weight'],
                                    'total'=>$row['weight']);
    }
  }
  $q="select header.name as type,
             p.fullname as contact,
             count(person.fullname) as weight
       from person,
            email,
            message,
            header,
            role,
            email as rec,
            person as p
       where person.fullname==:c and
             date(delivered)>=:start AND
             date(delivered)<:end and
             email.owner_id==person.id and
             message.sender_id==email.id and
             message.id==role.msg_id and
             role.email_id==rec.id and
             role.header_id==header.id and
             rec.owner_id==p.id
       group by contact, type
       having count(person.fullname)>1
       order by weight desc, contact
;";

  $query = $db->prepare($q);
  $query->execute(array(":c" => $c,
                        ":start" => $start,
                        ":end" => $end));
  for($i=0; $row = $query->fetch(); $i++){
    if(array_key_exists($row['contact'],$cache)){
      $cache[$row['contact']][$row['type']]=$row['weight'];
      $cache[$row['contact']]['total']+=$row['weight'];
    } else {
      $cache[$row['contact']]=array($row['type'] => $row['weight'], 'total'=>$row['weight']);
    }
  }

  uasort($cache,'cmpComposite');
  foreach($cache as $key => $item) {
    ?>
    <div class="person" id="<?php print $key;?>">
      <span style="float: left;"><a href="contact.php?c=<?php print urlencode($key)?>"><?php print $key?></a></span>
      <?php foreach($item as $type => $weight) {
              if(!strcmp($type,'total')) continue;
      ?>
            <div class="bar <?php print $type;?>" style="width: <?php print $weight;?>px;">
            <?php print $weight;?>
            </div>
      <?php } ?>
        <br />
        <div class="frequency">
           <div class="vaxis">
             <div class="max"></div>
             <div class="min"></div>
           </div>
           <div class="sparkline">Sparkline Loading...</div>
           <div class="haxis" >
              <span class="left"> </span>
              <span class="right"> </span>
           </div>
        </div>
    </div>
    <?php
  }
}

function cmpComposite ($a, $b) {
  if($a['total']==$b['total']) {
    return 0;
  }
  return ($a['total'] > $b['total']) ? -1 : 1;
}

?>
