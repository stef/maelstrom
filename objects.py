#!/usr/bin/python

from sqlobject import *
import psyco, sys, os

class Message(SQLObject):
   delivered=DateTimeCol()
   messageid=StringCol()
   headers=SQLMultipleJoin("HeaderValue")
   sender=ForeignKey("Email")
   path=StringCol()
   # TODO: if mailindexer
   # add path to raw
   # add paths to payloads
   # add path to mbox where message is stored

class Header(SQLObject):
   name=StringCol(unique=True)

class Email(SQLObject):
   username=StringCol()
   mailserver=StringCol()
   owner=ForeignKey('Person')

class Person(SQLObject):
   fullname=StringCol()

class Role(SQLObject):
   email=ForeignKey('Email')
   header=ForeignKey('Header')
   msg=ForeignKey('Message')
   
class HeaderValue(SQLObject):
   value=StringCol()
   msg=ForeignKey('Message')
   header=ForeignKey('Header')

def main():
   Header.createTable(ifNotExists=True)
   HeaderValue.createTable(ifNotExists=True)
   Person.createTable(ifNotExists=True)
   Email.createTable(ifNotExists=True)
   Role.createTable(ifNotExists=True)
   Message.createTable(ifNotExists=True)

sqlhub.processConnection = connectionForURI('sqlite:' + os.path.abspath('db/messages.db'))

if __name__=='__main__':
   psyco.full()
   sys.exit(main())
