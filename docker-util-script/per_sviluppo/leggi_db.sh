
DBNAME=$(docker compose exec -T nextcloud php occ config:system:get dbname | tr -d '\r')
DBUSER=$(docker compose exec -T nextcloud php occ config:system:get dbuser | tr -d '\r')
DBPASS=$(docker compose exec -T nextcloud php occ config:system:get dbpassword | tr -d '\r')
DBPFX=$(docker compose exec -T nextcloud php occ config:system:get dbtableprefix | tr -d '\r'); [ -z "$DBPFX" ] && DBPFX=oc_



docker compose exec -T db sh -lc "
mariadb -h 127.0.0.1 -u\"$DBUSER\" -p\"$DBPASS\" \"$DBNAME\" \
  -e \"SELECT id, user_id, path, size, mimetype, FROM_UNIXTIME(created_at) AS created_at
      FROM ${DBPFX}oc_coda_nuovi_files
      ORDER BY id DESC
      LIMIT 10;\"
"

docker compose exec -T db mariadb -u $DBUSER -p$DBPASS -D $DBNAME -e 'SELECT taskname,mimetype,executor_class FROM oc_mappa_executor_task; SELECT path,taskname,started,done FROM oc_tasks_attivi LIMIT 50; SELECT path,mimetype,created_at FROM oc_coda_nuovi_files LIMIT 50;'

#docker compose exec -T db mariadb -u egouser -pSb@r@gu@us -D nextcloud -e 'SELECT path,taskname,started,done FROM oc_tasks_attivi LIMIT 50;'
