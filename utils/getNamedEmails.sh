# Maelstrom - visualizing email contacts
# Copyright© 2008-2009 Stefan Marsiske <my name at gmail.com>

echo "select username, mailserver, fullname from email, person where owner_id==person.id;" | sqlite3 db/messages.db | sed -s 's/\(.*\)|\(.*\)|/\1@\2 / '
