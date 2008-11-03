#!/usr/bin/python

import sys, os, psyco, datetime, email
from sqlobject import *
from objects import *
from email.utils import getaddresses, parsedate
from utils import decode_header

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

def topReceivers():
   # SELECT person.id, person.fullname, person.username, person.mailserver FROMperson,  (SELECT DISTINCT message.sender_id AS senderID FROM person,message WHERE ((message.sender_id) = (person.id)))  Message_senderID WHERE((person.id) = (Message_senderID.senderID))
   results=Role._connection.queryAll("""SELECT count(person.id) as count,
                                               person.id,
                                               person.fullname,
                                               person.username,
                                               person.mailserver
                                        FROM person, role
                                        WHERE person.id==role.person_id
                                        GROUP BY person.id
                                        ORDER BY count DESC""")
   n=len(results)
   #q=Role.select().#throughTo.person
   #n=q.count()
   print "top receivers",n
   try:
      #for a in q:
      for a in results:
         #print a.fullname, a.username+"@"+a.mailserver
         print a
         #print a[0], a[2], a[3]+"@"+a[4]
   except(TypeError):
      print results

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
   topReceivers()
   #topSenders()
   #allPersons()
   #allMessages()
   #results(sentMessages())
   #results(receivedMessages())

if __name__=='__main__':
   psyco.full()
   sys.exit(main())
