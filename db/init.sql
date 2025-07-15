-- MariaDB initialization script
-- No hardcoded credentials. User and password are set by Kubernetes secrets and the official entrypoint.

-- Grant privileges if needed (optional, as entrypoint handles user creation)
-- GRANT ALL PRIVILEGES ON cevasdb.* TO 'cevasuser'@'%';
-- FLUSH PRIVILEGES; 