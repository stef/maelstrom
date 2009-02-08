#!/usr/bin/python

# Maelstrom - visualizing email contacts
# Copyright© 2008-2009 Stefan Marsiske <my name at gmail.com>

# BUGS: the mbox parse chockes on messages that have a line starting with From
# in the body.
import mailbox, sys, os, psyco, datetime, email, getopt
from sqlobject import *
from email.utils import getaddresses, parsedate
from lib.objects import *
from lib.utils import decode_header
from email.feedparser import FeedParser

SUPPORTEDFORMATS=['mbox', 'cyrus']
PERSONMAPFILE='db/persons.map'
personmap={}
config={}

def usage():
   print "usage: %s -d <mbox|cyrus>\n" % (sys.argv[0])
   print "\t-h                             This Help"
   print "\t-d <mbox|cyrus>                verbose message level"

class eMessage(email.Message.Message):
    """Message with mailbox-format-specific properties."""

    def __init__(self, message=None):
        """Initialize a Message instance."""
        feedparser = FeedParser(email.message.Message)
        feedparser._set_headersonly()
        data = message.read(4096)
        feedparser.feed(data)
        self._become_message(feedparser.close())

    def _become_message(self, message):
        """Assume the non-format-specific state of message."""
        for name in ('_headers', '_unixfrom', '_payload', '_charset',
                     'preamble', 'epilogue', 'defects', '_default_type'):
            self.__dict__[name] = message.__dict__[name]

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
         owner=fetchPerson(ownername,mail)
      else:
         owner=fetchPerson('',mail)

   q=Email.select(AND(Email.q.username==username,
               Email.q.mailserver==mailserver))
   try:
      return q.getOne()
   except(SQLObjectNotFound):
      return Email(username=username, mailserver=mailserver,owner=owner)

def fetchPerson(person,mail=None):
   if(not person):
      return
   if(mail and personmap.has_key(mail)):
      fullname=personmap[mail]
   else:
      fullname=decode_header(person).encode("utf-8").strip(" '\"")
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
      if(msg):
         parseContacts(message,msg)
         parseHeaders(message,msg)
      #TODO: mailindexer
      # parseBody(message,msg)

def parseMessage(message,file):
   # TODO refactor into own fun: create message
   if(config.decoder=="mbox"): 
      unixfrom=message.get_from().split(" ")
   if message['date']:
      t=parsedate(message['date'])
   elif(config.decoder=="mbox"): 
      t=parsedate(" ".join(unixfrom[1:]))
   try:
      timestamp=datetime.datetime(*t[:6])
   except:
      # pass this message with malformed header
      return None

   # fetch sender
   senders=getaddresses(message.get_all('from',[]))
   if len(senders):
      p=fetchPerson(decode_header(senders[0][0]).encode("utf-8"),senders[0][1])
      e=fetchEmail(senders[0][1],p)
   elif(config.decoder=="mbox"): 
      e=fetchEmail(unixfrom[0])
   #print "msg",msg
   return Message(delivered=timestamp,messageid=message['message-id'],sender=e,path=file)

def parseContacts(message,msg):
   for field in ["to","cc","resent-to","resent-cc"]:
      for address in getaddresses(message.get_all(field, [])):
         # fetch person
         p=fetchPerson(address[0],address[1])
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
   # load email to person mappings
   if(os.path.exists(PERSONMAPFILE)):
      fp=open(PERSONMAPFILE,'r')
      while(fp):
         line=fp.readline()
         if not line:
            break
         (email,name)=line.split(" ",1)
         personmap[email]=name.strip()
   for file in sys.argv[1:]:
      print "parsing message(s):", file 
      if(config.decoder=="mbox"):
         parseMbox(file)
      elif(config.decoder=="cyrus"):
         message=eMessage(open(file))
         msg=parseMessage(message,file)
         if(msg):
            parseContacts(message,msg)
            parseHeaders(message,msg)

if __name__=='__main__':
   try:
       opts, args = getopt.gnu_getopt(sys.argv[1:],
                                      "hd:",
                                      ["help",
                                       "decoder="])
   except getopt.GetoptError:
       usage()
       sys.exit(2)
  
   for o, a in opts:
      if o in ("-h", "--help"):
         usage()
         sys.exit()
      elif o in ("-d", "--decoder"):
         if(a and a in SUPPORTEDFORMATS):
            config.decoder = a
         else:
            usage()
            sys.exit()
   Header.createTable(ifNotExists=True)
   HeaderValue.createTable(ifNotExists=True)
   Person.createTable(ifNotExists=True)
   Role.createTable(ifNotExists=True)
   Email.createTable(ifNotExists=True)
   Message.createTable(ifNotExists=True)
   psyco.full()
   sys.exit(main())
