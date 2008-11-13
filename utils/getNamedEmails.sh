echo "select username, mailserver, fullname from email, person where owner_id==person.id;" | sqlite3 db/messages.db | sed -s 's/\(.*\)|\(.*\)|/\1@\2 / '
