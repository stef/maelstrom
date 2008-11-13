echo "select * from email where owner_id is null;" | sqlite3 db/messages.db | sed -s 's/.*|\(.*\)|\(.*\)|/\1@\2/'
