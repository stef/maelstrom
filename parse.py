#!/usr/bin/python

import mailbox, sys, os, psyco, datetime, email
from sqlobject import *
from email.utils import getaddresses, parsedate
from objects import *
from utils import decode_header

def fetchEmail(mail,owner=None):
   if(not mail):
      return

   username=''
   mailserver=''
   parts=mail.split("@")
   if(len(parts)>2):
      print mail
      print "more than 2 elements to a split emailaddress, bailing out"
      sys.exit(1)
   elif(len(parts)==2):
      mailserver=parts[1].lower()
   username=parts[0].lower()

   if(not owner):
      owner=''
      names=username.split('.')
      if(len(names)>1):
         ownername=" ".join(map(lambda x: x[0].upper()+x[1:],names))
         owner=fetchPerson(ownername)
      else:
         owner=fetchPerson('')

   q=Email.select(AND(Email.q.username==username,
               Email.q.mailserver==mailserver))
   try:
      return q.getOne()
   except(SQLObjectNotFound):
      return Email(username=username, mailserver=mailserver,owner=owner)

def fetchPerson(person):
   if(not person):
      return
   fullname=decode_header(person).encode("utf-8")
   q=Person.select(Person.q.fullname==fullname)
   try:
      return q.getOne()
   except(SQLObjectNotFound):
      return Person(fullname=fullname)

def fetchHeader(header):
   q=Header.select(Header.q.name==header)
   try:
      return q.getOne()
   except(SQLObjectNotFound):
      return Header(name=header.encode("utf-8"))
      #print "header",h
   except(SQLObjectIntegrityError):
      print "oops. database headers probably contains multiple entries for", header
      sys.exit(1)

def parseMbox(file):
   for message in mailbox.mbox(file):
      msg=parseMessage(message,file)
      parseContacts(message,msg)
      parseHeaders(message,msg)
      #TODO: mailindexer
      # parseBody(message,msg)

def parseMessage(message,file):
   # TODO refactor into own fun: create message
   unixfrom=message.get_from().split(" ")
   if message['date']:
      t=parsedate(message['date'])
   else:
      t=parsedate(" ".join(unixfrom[1:]))
   timestamp=datetime.datetime(*t[:6])

   # fetch sender
   senders=getaddresses(message.get_all('from',[]))
   if len(senders):
      p=fetchPerson(decode_header(senders[0][0]).encode("utf-8"))
      e=fetchEmail(senders[0][1],p)
   else: 
      e=fetchEmail(unixfrom[0])
   #print "msg",msg
   return Message(delivered=timestamp,messageid=message['message-id'],sender=e,path=file)

def parseContacts(message,msg):
   for field in ["to","cc","resent-to","resent-cc"]:
      for address in getaddresses(message.get_all(field, [])):
         # fetch person
         p=fetchPerson(address[0])
         e=fetchEmail(address[1],p)
         # fetch header
         h=fetchHeader(field)
         Role(email=e,msg=msg,header=h)
         #print "role",role
      del message[field]

def parseHeaders(message,msg):
   for header in map(str.lower,message.keys()):
      h=fetchHeader(header)
      for value in message.get_all(header, []):
         value=decode_header(value).encode("utf-8")
         hv=HeaderValue(value=value,msg=msg,header=h)

def parseBody(message,msg):
   for part in message.walk():
      if part.get_content_maintype() == 'multipart':
         continue
      print part.get_content_type(), decode_header(part.get_filename('')).encode("utf-8")
      # TODO: if used as mailindexer
      # store payloads, possibly with hashed filenames
      # feed them to xapian
#     print part.get_payload(decode=True)
#     # Applications should really sanitize the given filename so that an
#     # email message can't be used to overwrite important files
#     filename = part.get_filename()
#     if not filename:
#        ext = mimetypes.guess_extension(part.get_content_type())
#        if not ext:
#            # Use a generic bag-of-bits extension
#            ext = '.bin'
#        filename = 'part-%03d%s' % (counter, ext)
#     counter += 1
#     fp = open(os.path.join(opts.directory, filename), 'wb')
#     fp.write(part.get_payload(decode=True))
#     fp.close()

def main():
   for file in sys.argv[1:]:
      print "parsing file:", file
      parseMbox(file)

if __name__=='__main__':
   Header.createTable(ifNotExists=True)
   HeaderValue.createTable(ifNotExists=True)
   Person.createTable(ifNotExists=True)
   Role.createTable(ifNotExists=True)
   Email.createTable(ifNotExists=True)
   Message.createTable(ifNotExists=True)
   psyco.full()
   sys.exit(main())
