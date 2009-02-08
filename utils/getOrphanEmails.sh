# Maelstrom - visualizing email contacts
# Copyright© 2008-2009 Stefan Marsiske <my name at gmail.com>
echo "select * from email where owner_id is null;" | sqlite3 db/messages.db | sed -s 's/.*|\(.*\)|\(.*\)|/\1@\2/'
