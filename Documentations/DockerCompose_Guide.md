# Docker & Docker Compose Exercise Guide

## Overview
This guide will walk you through building, running, and understanding a simple multi-container system using Docker and Docker Compose. The system consists of an `app` service (your application) and a `database` service (MariaDB). You will learn how to containerize both, connect them, persist data, and manage everything with Docker Compose.

---

## 1. Learning Objectives
- **Understand containerization**: Learn how to package applications and databases as containers.
- **Build Docker images**: Write and use Dockerfiles for custom app images.
- **Network containers**: Connect multiple containers using Docker networks.
- **Persist data**: Use Docker volumes for database persistence.
- **Environment variables**: Pass configuration and secrets securely.
- **Simplify orchestration**: Use Docker Compose to manage multi-container setups.
- **Best practices**: Learn why these steps matter for real-world development and deployment.

---

## 2. Project Structure

- `app/` — Your application code and Dockerfile
- `db/` — Database schema and Dockerfile
- `docker-compose.yaml` — Compose file to orchestrate services
- `Makefile` — (Optional) For simplified commands

---

## 3. Step-by-Step Guide

### **Step 1: Build the App Service Container**

- **Dockerfile location:** `app/Dockerfile`
- **Key Directives:**
  - `FROM` — Base image (e.g., PHP, Node, Python, etc.)
  - `MAINTAINER` — Author info
  - `WORKDIR` — Set working directory
  - `COPY` — Copy app code into the image
  - `RUN` — Install dependencies
  - `EXPOSE` — Expose app port

**Example:**
```dockerfile
FROM php:8.1-apache
MAINTAINER Your Name <your@email.com>
WORKDIR /var/www/html
COPY . .
RUN docker-php-ext-install pdo pdo_mysql
EXPOSE 80
```

**Build the image:**
```bash
docker build -t myapp:latest ./app
```

---

### **Step 2: Build the Database Service Container**

- **Dockerfile location:** `db/Dockerfile`
- **Key Directives:**
  - `FROM` — Use `mariadb` or `mysql` official image
  - `COPY` — Add schema or init scripts

**Example:**
```dockerfile
FROM mariadb:latest
COPY schema.sql /docker-entrypoint-initdb.d/
```

**Build the image:**
```bash
docker build -t mydb:latest ./db
```

---

### **Step 3: Create a Custom Docker Network**

This allows containers to communicate securely.

```bash
docker network create --driver bridge mynetwork
```

---

### **Step 4: Run the Database Container with a Volume**

- **Volume**: Ensures data persists even if the container is removed.
- **User-defined name**: For easy reference.

```bash
docker run -d \
  --name mydb \
  --network mynetwork \
  -v mydbdata:/var/lib/mysql \
  -e MYSQL_ROOT_PASSWORD=test \
  -e MYSQL_DATABASE=testdb \
  -e MYSQL_USER=testuser \
  -e MYSQL_PASSWORD=test \
  mydb:latest
```

---

### **Step 5: Run the App Container**

- **Connect to the same network**
- **Pass DB credentials as environment variables**
- **Expose the app port to the host**
- **User-defined name**

```bash
docker run -d \
  --name myapp \
  --network mynetwork \
  -e DB_HOST=testdb \
  -e DB_NAME=testdb \
  -e DB_USER=testuser \
  -e DB_PASS=test \
  -p 8080:80 \
  myapp:latest
```

- Now, your app is accessible at [http://localhost:8080](http://localhost:8080)
- The app can connect to the database using the service name `mydb` (Docker DNS)

---

### **Step 6: Test the System**
- Open your browser to [http://localhost:8080](http://localhost:8080)
- Try creating and retrieving data (e.g., sign up, log in, etc.)
- Data should persist even if you stop and restart the database container

---

### **Step 7: Use Docker Compose for Orchestration**

- **File:** `docker-compose.yaml`
- **Purpose:** Define and run multi-container Docker applications with a single command

**Example:**
```yaml
version: '3.8'
services:
  db:
    image: mydb:latest
    container_name: mydb
    environment:
      MYSQL_ROOT_PASSWORD: test
      MYSQL_DATABASE: testdb
      MYSQL_USER: testuser
      MYSQL_PASSWORD: test
    volumes:
      - mydbdata:/var/lib/mysql
    networks:
      - mynetwork
  app:
    image: myapp:latest
    container_name: myapp
    environment:
      DB_HOST: db
      DB_NAME: testdb
      DB_USER: testuser
      DB_PASS: test
    ports:
      - "8080:80"
    depends_on:
      - db
    networks:
      - mynetwork
volumes:
  mydbdata:
networks:
  mynetwork:
    driver: bridge
```

**Start everything:**
```bash
docker-compose up -d
```

**Stop everything:**
```bash
docker-compose down
```

---

## 4. (Optional) Makefile for Convenience

- **File:** `Makefile`
- **Purpose:** Simplify common Docker and Compose commands

**Example targets:**
```makefile
build:
	docker-compose build
up:
	docker-compose up -d
down:
	docker-compose down
logs:
	docker-compose logs -f
```

---

## 5. Why Do This? (Importance & Learning Objectives)

- **Isolation:** Containers let you run your app and database in isolated, reproducible environments.
- **Portability:** Your setup works the same on any machine with Docker.
- **Networking:** Learn how containers communicate securely via custom networks.
- **Persistence:** Volumes ensure your data survives container restarts.
- **Security:** Credentials are passed as environment variables, not hardcoded.
- **Orchestration:** Docker Compose makes it easy to manage multi-container systems.
- **Real-World Skills:** These are foundational DevOps and cloud-native skills used in modern software engineering.

---

## 6. Troubleshooting & Tips
- If the app can't connect to the DB, check network names and environment variables.
- Use `docker-compose logs` or `docker logs <container>` to debug.
- Remove volumes with `docker volume rm mydbdata` if you want a fresh DB.
- Always stop containers with `docker-compose down` to clean up networks.

---

## 7. Next Steps
- Try adding a reverse proxy (nginx) as a third service.
- Experiment with scaling the app service.
- Add health checks to your database service in Compose.

---

**Congratulations!**
You now understand how to build, connect, and orchestrate multi-container systems with Docker and Docker Compose. 