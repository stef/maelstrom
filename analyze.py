#!/usr/bin/python

import sys, os, psyco, datetime, email
from sqlobject import *
from email.utils import getaddresses, parsedate
from lib.objects import *
from lib.utils import decode_header
from viz.tagcloud import drawCloud

def results(q):
   print "q",q
   try:
      for item in q:
         print item
   except(SQLObjectNotFound):
      print "no items found"

def sentMessages():
   return Role.select(AND(Role.q.header==Header.q.id,
                        Role.q.msg==Message.q.id,
                        Role.q.person==Person.q.id,
                        OR(Person.q.email=="stef@ctrlc.hu",
                           Person.q.email=="stefan.marsiske@gmail.com",
                           Person.q.email=="marsiskes@gmail.com",
                           Person.q.email=="stefan.marsiske@liberit.hu"),
                        Header.q.name=="from")).throughTo.msg
def receivedMessages():
   return Role.select(AND(Role.q.header==Header.q.id, 
                            Role.q.msg==Message.q.id,
                            Role.q.person==Person.q.id,
                            OR(Person.q.email=="stef@ctrlc.hu",
                               Person.q.email=="stefan.marsiske@gmail.com",
                               Person.q.email=="marsiskes@gmail.com",
                               Person.q.email=="stefan.marsiske@liberit.hu"),
                            OR(Header.q.name=="to",
                               Header.q.name=="cc",
                               Header.q.name=="resent-to",
                               Header.q.name=="resent-cc"))).throughTo.msg

def allMessages():
   print Message.select().count()
   for a in Message.select():
      print a

def topReceiversWindow(days):
   q=Message.select(orderBy="delivered")
   try:
      start=(q[0].delivered)
   except(SQLObjectNotFound):
      print "empty db, bailing out."
      sys.exit(1)
   q=Message.select(orderBy="delivered").reversed()
   try:
      last=(q[0].delivered)
   except(SQLObjectNotFound):
      print "empty db, bailing out."
      sys.exit(1)
   i=0
   while start<last:
      end=start+datetime.timedelta(days)
      print end
      
      q="""SELECT person.fullname,
                  count(person.id) as count
           FROM person, email, role, message
           WHERE role.email_id==email.id AND 
                 email.owner_id==person.id AND
                 role.msg_id==message.id AND
                 message.delivered>='"""+start.date().isoformat()+"""' AND
                 message.delivered<'"""+end.date().isoformat()+"""'
           GROUP BY person.fullname
           ORDER BY count DESC"""
      results=Message._connection.queryAll(q)
      n=len(results)
      tags=sorted(results)
      img=drawCloud(tags)
      img.save("tmp/receivers-"+str(i)+".png") 
      start=start+datetime.timedelta(2)
      i+=1

def topReceivers():
   q="""SELECT person.fullname,
               count(person.id) as count
        FROM person, email, role
        WHERE role.email_id==email.id AND 
              email.owner_id==person.id
        GROUP BY person.fullname
        HAVING count>1
        ORDER BY count DESC"""
   results=Role._connection.queryAll(q)
   n=len(results)
   tags=sorted(results)
   drawCloud(tags).save("topreceivers.png") 

def topSenders():
   q=Message.select(Message.q.sender==Person.q.id).throughTo.sender
   print q
   n=q.count()
   print
   print "top senders",n
   try:
      for a in q:
         print a.fullname, a.username+"@"+a.mailserver
   except:
      print q

def allPersons():
   #print Person.select().count()
   #for a in Person.select():
   #   print a.name, a.email

   q=Person.select(Person.q.fullname=="")
   n=q.count()
   print
   print "nameless persons",n
   try:
      for a in q:
         print a.username+"@"+a.mailserver
   except:
      print q

   q=Person.select(OR(Person.q.username=="",Person.q.mailserver==""))
   n=q.count()
   print
   print "addressless persons", n
   try:
      for a in q:
         print a.fullname
   except(TypeError):
      print q

def main():
   topReceiversWindow(30)
   #topReceivers()
   #topSenders()
   #allPersons()
   #allMessages()
   #results(sentMessages())
   #results(receivedMessages())

if __name__=='__main__':
   psyco.full()
   sys.exit(main())
