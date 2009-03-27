#!/usr/bin/python
"""
Maelstrom - visualizing email contacts

Copyright(c) 2008-2009 Stefan Marsiske <my name at gmail.com>

database layer for maelstrom
"""

import sqlobject
import sys, os, platform
if(platform.machine()=='i686'):
   import psyco

from utils import CFG
DBPATH = CFG.get('maelstrom','database')
sqlobject.sqlhub.processConnection = sqlobject.connectionForURI('sqlite:' + DBPATH)

class Message(sqlobject.SQLObject):
    """ represents a message object """
    delivered = sqlobject.col.DateTimeCol()
    messageid = sqlobject.col.StringCol()
    headers = sqlobject.SQLMultipleJoin("HeaderValue")
    sender = sqlobject.col.ForeignKey("Email")
    path = sqlobject.col.StringCol()
    # TODO: if mailindexer
    # add path to raw
    # add paths to payloads
    # add path to mbox where message is stored

class Header(sqlobject.SQLObject):
    """ Represents a header object, this is stored uniquely in a
    separate table, headervalues reference these"""
    name = sqlobject.col.StringCol(unique = True)

class Email(sqlobject.SQLObject):
    """ represents a email object, it consists of an
    <username>@<mailserver> and an associated owner"""
    username = sqlobject.col.StringCol()
    mailserver = sqlobject.col.StringCol()
    owner = sqlobject.col.ForeignKey('Person')
    def getname(self):
       """
       returns the most specific name for an email correspondent
       """
       if(self.owner):
          return self.owner.fullname
       return self.username+"@"+self.mailserver

class Person(sqlobject.SQLObject):
    """ represents a person, currently only stores the name"""
    fullname = sqlobject.col.StringCol()

class Role(sqlobject.SQLObject):
    """ represents the role of a person in respect to an email, we
    link a message, with an email address and the according header
    (cc, to)"""
    email = sqlobject.col.ForeignKey('Email')
    header = sqlobject.col.ForeignKey('Header')
    msg = sqlobject.col.ForeignKey('Message')

class HeaderValue(sqlobject.SQLObject):
    """ this represents a header set in a message, the msg is linked
    to the header and a value is associated."""
    value = sqlobject.col.StringCol()
    msg = sqlobject.col.ForeignKey('Message')
    header = sqlobject.col.ForeignKey('Header')

""" if being executed instead of loaded as a module, create a new
database"""
if (__name__ == '__main__'):
   def main():
      """ this function creates a new database"""
      Header.createTable(ifNotExists = True)
      HeaderValue.createTable(ifNotExists = True)
      Person.createTable(ifNotExists = True)
      Email.createTable(ifNotExists = True)
      Role.createTable(ifNotExists = True)
      Message.createTable(ifNotExists = True)

   psyco.full()
   sys.exit(main())
