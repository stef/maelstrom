#!/usr/bin/python

import mailbox, sys, os, psyco, datetime, email
from sqlobject import *
from email.utils import getaddresses, parsedate
from objects import *
from utils import decode_header

def fetchPerson(person):
   if(person[0]):
      fullname=decode_header(person[0]).encode("utf-8")
   else:
      fullname=''

   username=''
   mailserver=''
   if(person[1]):
      mail=person[1].split("@")
      if(len(mail)>2):
         print person
         print "more than 2 elements to a split emailaddress, bailing out"
         sys.exit(1)
      elif(len(mail)==2):
         mailserver=mail[1].lower()
      username=mail[0].lower()

   q=Person.select(AND(Person.q.username==username,
                       Person.q.mailserver==mailserver))
   try:
      return q.getOne()
   except(SQLObjectNotFound):
      return Person(fullname=fullname,
                    username=username,
                    mailserver=mailserver)
   except(SQLObjectIntegrityError):
      print "oops. database headers probably contains multiple entries for", header
      sys.exit(1)

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
      unixfrom=message.get_from().split(" ")
      if message['date']:
         t=parsedate(message['date'])
      else:
         t=parsedate(" ".join(unixfrom[1:]))
      timestamp=datetime.datetime(*t[:6])

      # fetch sender
      senders=getaddresses(message.get_all('from',[]))
      if len(senders):
         s=fetchPerson(senders[0])
      else: 
         s=fetchPerson((unixfrom[0],''))
      msg=Message(delivered=timestamp,messageid=message['message-id'],sender=s,path=file)
      #print "msg",msg

      for field in ["to","cc","resent-to","resent-cc"]:
         for address in getaddresses(message.get_all(field, [])):
            # fetch person
            p=fetchPerson(address)

            # fetch header
            h=fetchHeader(field)

            role=Role(person=p,msg=msg,header=h)
            #print "role",role
         del message[field]

      parseHeaders(message,msg)
      #TODO: mailindexer
      # parseBody(message,msg)

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
   sqlhub.processConnection = connectionForURI('sqlite:' + os.path.abspath('messages.db'))
   Header.createTable(ifNotExists=True)
   HeaderValue.createTable(ifNotExists=True)
   Person.createTable(ifNotExists=True)
   Role.createTable(ifNotExists=True)
   Message.createTable(ifNotExists=True)
   psyco.full()
   sys.exit(main())
