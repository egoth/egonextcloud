
DBNAME=$(docker compose exec -T nextcloud php occ config:system:get dbname | tr -d '\r')
DBUSER=$(docker compose exec -T nextcloud php occ config:system:get dbuser | tr -d '\r')
DBPASS=$(docker compose exec -T nextcloud php occ config:system:get dbpassword | tr -d '\r')
DBPFX=$(docker compose exec -T nextcloud php occ config:system:get dbtableprefix | tr -d '\r'); [ -z "$DBPFX" ] && DBPFX=oc_



docker compose exec -T db sh -lc "
mariadb -h 127.0.0.1 -u\"$DBUSER\" -p\"$DBPASS\" \"$DBNAME\" \
  -e \"SELECT id, user_id, path, size, mimetype, FROM_UNIXTIME(created_at) AS created_at
      FROM ${DBPFX}egonextapp_coda
      ORDER BY id DESC
      LIMIT 10;\"
"
