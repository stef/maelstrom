"""
Maelstrom - visualizing email contacts

Copyright(c) 2008-2009 Stefan Marsiske <my name at gmail.com>
"""

import email
import csv
import cStringIO
import codecs

def decode_header(text):
   """Decode a header value and return the value as a unicode string."""
   if not text:
       return text
   res = []
   for part, charset in email.Header.decode_header(text):
      try:
         res.append(part.decode(charset or 'latin1', 'replace'))
      except LookupError: # If charset is unknown
         res.append(part.decode('latin1', 'replace'))
   return ' '.join(res)


class Obj:
   """
   abstract baseclass for node,edge,graph
   """
   def __getattr__(self, name):
      if(self.__dict__.has_key(name)):
         return self.__dict__[name]
      else:
         raise AttributeError, name

   def __setattr__(self, name, value):
      if(self.__dict__.has_key(name)):
         self.__dict__[name] = value
      else:
         raise AttributeError, name

   def __repr__(self):
      return self.__str__()

   def __str__(self):
      return reduce(lambda y, x: "%s%s: %s\n" % (y,
                                                 x,
                                                 repr(self.__dict__[x])),
                    self.__dict__.keys())

class UnicodeWriter:
   """
   A CSV writer which will write rows to CSV file "f",
   which is encoded in the given encoding.
   """
   def __init__(self, f, dialect=csv.excel, encoding="utf-8", **kwds):
      # Redirect output to a queue
      self.queue = cStringIO.StringIO()
      self.writer = csv.writer(self.queue, dialect=dialect, **kwds)
      self.stream = f
      self.encoder = codecs.getincrementalencoder(encoding)()

   def writerow(self, row):
      #ORIG:self.writer.writerow([s.encode("utf-8") for s in row])
      self.writer.writerow(row)
      # Fetch UTF-8 output from the queue ...
      data = self.queue.getvalue()
      data = data.decode("utf-8")
      # ... and reencode it into the target encoding
      data = self.encoder.encode(data)
      # write to the target stream
      self.stream.write(data)
      # empty queue
      self.queue.truncate(0)


def counter(start=0):
   """
   auto incrementing id generator
   """
   while True:
      start += 1
      yield start
