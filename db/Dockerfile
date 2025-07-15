FROM mariadb:latest

# No hardcoded credentials. All credentials are provided by Kubernetes secrets at runtime.
COPY init.sql /docker-entrypoint-initdb.d/01-init.sql
COPY schema.sql /docker-entrypoint-initdb.d/02-schema.sql 