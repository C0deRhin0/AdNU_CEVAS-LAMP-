# Complete Kubernetes Deployment Guide for AdNU CEVAS LAMP Stack

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Understanding the Architecture](#understanding-the-architecture)
3. [Step 1: Environment Setup](#step-1-environment-setup)
4. [Step 2: Docker Registry Authentication](#step-2-docker-registry-authentication)
5. [Step 3: Kubernetes Namespace](#step-3-kubernetes-namespace)
6. [Step 4: Understanding Kubernetes Resources](#step-4-understanding-kubernetes-resources)
7. [Step 5: Creating ConfigMap](#step-5-creating-configmap)
8. [Step 6: Creating Secret](#step-6-creating-secret)
9. [Step 7: Creating PersistentVolume and PVC](#step-7-creating-persistentvolume-and-pvc)
10. [Step 8: Database Deployment](#step-8-database-deployment)
11. [Step 9: Application Deployment](#step-9-application-deployment)
12. [Step 10: Proxy Deployment](#step-10-proxy-deployment)
13. [Step 11: Deployment Execution](#step-11-deployment-execution)
14. [Step 12: Verification and Testing](#step-12-verification-and-testing)
15. [Troubleshooting](#troubleshooting)
16. [Cleanup](#cleanup)

---

## Prerequisites

Before starting this guide, ensure you have:

### 1. Docker Desktop with Kubernetes Enabled
- **What it is**: Docker Desktop includes a single-node Kubernetes cluster
- **How to check**: Open Docker Desktop → Settings → Kubernetes → Enable Kubernetes
- **Why needed**: Provides a local Kubernetes environment for testing

### 2. kubectl Command Line Tool
- **What it is**: Command-line interface for running commands against Kubernetes clusters
- **How to check**: Run `kubectl version --client`
- **Installation**: Usually comes with Docker Desktop, or install via:
  ```bash
  # macOS
  brew install kubectl
  
  # Or download from official site
  curl -LO "https://dl.k8s.io/release/$(curl -L -s https://dl.k8s.io/release/stable.txt)/bin/darwin/amd64/kubectl"
  chmod +x kubectl
  sudo mv kubectl /usr/local/bin/
  ```

### 3. Docker Images in Private Registry
- **What you have**: Images pushed to `c0derhin0/adnu-cevas-lamp`
- **Tags**: `:app`, `:db`, `:proxy`
- **Why private**: Kubernetes needs authentication to pull these images

### 4. Docker Registry Credentials
- **Username**: Your Docker Hub username
- **Password**: Your Docker Hub password or access token
- **Email**: Your Docker Hub email

---

## Understanding the Architecture

### What is a LAMP Stack?
**LAMP** stands for:
- **L**inux (Operating System)
- **A**pache (Web Server) - In our case, Nginx as proxy
- **M**ySQL/MariaDB (Database)
- **P**HP (Programming Language)

### Our Kubernetes Architecture
```
Internet → LoadBalancer Service → Nginx Proxy → App Service → PHP Application
                                                    ↓
                                              Database Service → MariaDB
```

### Components Breakdown:
1. **Proxy (Nginx)**: Reverse proxy that receives external traffic
2. **App (PHP)**: Your web application
3. **DB (MariaDB)**: Database server
4. **Services**: Network endpoints for communication
5. **PersistentVolume**: Storage for database data
6. **ConfigMap**: Configuration data
7. **Secret**: Sensitive data (passwords)

---

## Step 1: Environment Setup

### 1.1 Verify Docker Desktop Kubernetes
```bash
# Check if Kubernetes is running
kubectl cluster-info

# Expected output:
# Kubernetes control plane is running at https://kubernetes.docker.internal:6443
# CoreDNS is running at https://kubernetes.docker.internal:6443/api/v1/namespaces/kube-system/services/kube-dns:dns/proxy
```

### 1.2 Check kubectl Context
```bash
# View current context
kubectl config current-context

# Should show: docker-desktop

# View all contexts
kubectl config get-contexts

# Switch to docker-desktop if needed
kubectl config use-context docker-desktop
```

### 1.3 Verify Node Status
```bash
# Check if your node is ready
kubectl get nodes

# Expected output:
# NAME             STATUS   ROLES           AGE   VERSION
# docker-desktop   Ready    control-plane   1d    v1.27.4
```

---

## Step 2: Docker Registry Authentication

### 2.1 Why We Need This
Kubernetes needs credentials to pull your private Docker images from `c0derhin0/adnu-cevas-lamp`.

### 2.2 Create Docker Registry Secret
```bash
# Replace <USER>, <PASS>, <EMAIL> with your actual credentials
kubectl create secret docker-registry regcred \
  --docker-server=https://index.docker.io/v1/ \
  --docker-username=<USER> \
  --docker-password=<PASS> \
  --docker-email=<EMAIL>
```

**Example:**
```bash
kubectl create secret docker-registry regcred \
  --docker-server=https://index.docker.io/v1/ \
  --docker-username=c0derhin0 \
  --docker-password=your_password_here \
  --docker-email=your_email@example.com
```

### 2.3 Verify Secret Creation
```bash
# List secrets
kubectl get secrets

# Should show:
# NAME       TYPE                             DATA   AGE
# regcred    kubernetes.io/dockerconfigjson   1      1m

# View secret details
kubectl describe secret regcred
```

---

## Step 3: Kubernetes Namespace

### 3.1 What is a Namespace?
A namespace is like a folder in Kubernetes that groups related resources together. It provides isolation and organization.

### 3.2 Create Namespace
```bash
# Create the namespace
kubectl create namespace adnu-cevas

# Verify creation
kubectl get namespaces

# Should show adnu-cevas in the list
```

### 3.3 Set Default Namespace
```bash
# Set the current context to use adnu-cevas namespace
kubectl config set-context --current --namespace=adnu-cevas

# Verify current namespace
kubectl config view --minify --output 'jsonpath={..namespace}'

# Should output: adnu-cevas
```

---

## Step 4: Understanding Kubernetes Resources

### 4.1 ServiceAccount
**What it is**: An identity for pods to authenticate with the Kubernetes API
**Why needed**: To use the Docker registry secret we created

**File**: `serviceaccount.yaml`
```yaml
apiVersion: v1
kind: ServiceAccount
metadata:
  name: registry-sa
imagePullSecrets:
  - name: regcred
```

**Apply it:**
```bash
kubectl apply -f serviceaccount.yaml
```

### 4.2 ConfigMap
**What it is**: Stores non-confidential configuration data
**Why needed**: To store database connection parameters

**File**: `configmap.yaml`
```yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: db-config
data:
  DB_HOST: "db-service"
  DB_NAME: "cevasdb"
  DB_USER: "cevasuser"
  DB_PASS: "rhino"
  MYSQL_ROOT_PASSWORD: "root"
  MYSQL_DATABASE: "cevasdb"
  MYSQL_USER: "cevasuser"
  MYSQL_PASSWORD: "rhino"
```

**Key Points:**
- `DB_HOST: "db-service"` - Points to the database service name
- All values are strings (quoted)
- No sensitive data (passwords are in Secret)

### 4.3 Secret
**What it is**: Stores sensitive data like passwords
**Why needed**: To securely store database credentials

**File**: `secret.yaml`
```yaml
apiVersion: v1
kind: Secret
metadata:
  name: db-secret
type: Opaque
data:
  mysql-root-password: cm9vdA==  # "root" in base64
  mysql-password: cmhpbm8=       # "rhino" in base64
```

**How to encode values:**
```bash
# Encode a string to base64
echo -n "your_password" | base64

# Decode base64 to verify
echo "cm9vdA==" | base64 -d
```

### 4.4 PersistentVolume (PV)
**What it is**: Physical storage in the cluster
**Why needed**: To persist database data across pod restarts

**File**: `persistentvolume.yaml`
```yaml
apiVersion: v1
kind: PersistentVolume
metadata:
  name: db-pv
spec:
  capacity:
    storage: 1Gi
  accessModes:
    - ReadWriteOnce
  hostPath:
    path: "/tmp/k8s-db-data"
  storageClassName: host
```

**Key Points:**
- `capacity.storage: 1Gi` - 1 gigabyte of storage
- `accessModes: ReadWriteOnce` - Can be mounted by one node
- `hostPath.path: "/tmp/k8s-db-data"` - Physical path on host machine
- `storageClassName: host` - Uses host storage (not cloud storage)

### 4.5 PersistentVolumeClaim (PVC)
**What it is**: Request for storage by a pod
**Why needed**: Pods request storage through PVCs, not directly from PVs

**File**: `persistentvolume.yaml` (same file, after PV)
```yaml
---
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: db-pvc
spec:
  accessModes:
    - ReadWriteOnce
  resources:
    requests:
      storage: 1Gi
  storageClassName: host
```

**Key Points:**
- `storage: 1Gi` - Requests 1GB (matches PV capacity)
- `storageClassName: host` - Must match PV's storage class

---

## Step 5: Creating ConfigMap

### 5.1 Apply ConfigMap
```bash
kubectl apply -f configmap.yaml
```

### 5.2 Verify ConfigMap
```bash
# List ConfigMaps
kubectl get configmaps

# Should show:
# NAME        DATA   AGE
# db-config   8      1m

# View ConfigMap details
kubectl describe configmap db-config

# View ConfigMap data
kubectl get configmap db-config -o yaml
```

---

## Step 6: Creating Secret

**IMPORTANT:**
Before deploying, you must create a Kubernetes Secret for your database and application credentials.

1. Copy the template:
   ```bash
   cp k8s/secret.yaml.template k8s/secret.yaml
   ```
2. Edit `k8s/secret.yaml` and fill in your real credentials (all values must be base64 encoded).
3. Apply the secret:
   ```bash
   kubectl apply -f k8s/secret.yaml
   ```

**Never commit your real `secret.yaml` to a public repository!**

- The `db-deployment.yaml` and `app-deployment.yaml` now reference credentials from the secret, not the configmap.
- Only non-sensitive config (like DB_HOST, DB_NAME) remains in the configmap.

**Security Best Practices:**
- Always use Kubernetes Secrets for sensitive data.
- Add `k8s/secret.yaml` to your `.gitignore`.
- Use strong, unique passwords for all credentials.
- For production, consider using an external secret manager (e.g., HashiCorp Vault, AWS Secrets Manager).

---

## Step 7: Creating PersistentVolume and PVC

### 7.1 Apply PV and PVC
```bash
kubectl apply -f persistentvolume.yaml
```

### 7.2 Verify PV and PVC
```bash
# List PersistentVolumes
kubectl get pv

# Should show:
# NAME   CAPACITY   ACCESS MODES   RECLAIM POLICY   STATUS      CLAIM   STORAGECLASS   REASON   AGE
# db-pv  1Gi        RWO            Retain           Available           host                   1m

# List PersistentVolumeClaims
kubectl get pvc

# Should show:
# NAME    STATUS    VOLUME   CAPACITY   ACCESS MODES   STORAGECLASS   AGE
# db-pvc  Bound     db-pv    1Gi        RWO            host           1m
```

**Status Explanation:**
- `Available` - PV is ready to be claimed
- `Bound` - PVC is successfully bound to PV

---

## Step 8: Database Deployment

### 8.1 Understanding the Database Deployment

**File**: `db-deployment.yaml`

**Key Sections:**

#### Deployment Metadata
```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: db-deployment
spec:
  replicas: 1  # Only one database instance
```

#### Pod Template
```yaml
spec:
  serviceAccountName: registry-sa  # Uses our ServiceAccount
  containers:
  - name: db
    image: c0derhin0/adnu-cevas-lamp:db
    ports:
    - containerPort: 3306  # MariaDB default port
```

#### Environment Variables
```yaml
env:
- name: MYSQL_ROOT_PASSWORD
  valueFrom:
    configMapKeyRef:
      name: db-config
      key: MYSQL_ROOT_PASSWORD
```

**Why ConfigMap**: Instead of hardcoding values, we reference the ConfigMap.

#### Volume Mounts
```yaml
volumeMounts:
- name: db-storage
  mountPath: /var/lib/mysql  # MariaDB data directory
```

#### Health Checks
```yaml
livenessProbe:
  exec:
    command:
    - mariadb-admin
    - ping
    - -h
    - localhost
  initialDelaySeconds: 30  # Wait 30s before first check
  periodSeconds: 10        # Check every 10s
```

**What it does**: Ensures the database is healthy and restarts if it's not.

#### Volumes
```yaml
volumes:
- name: db-storage
  persistentVolumeClaim:
    claimName: db-pvc  # References our PVC
```

### 8.2 Database Service
```yaml
apiVersion: v1
kind: Service
metadata:
  name: db-service
spec:
  selector:
    app: db  # Targets pods with label app=db
  ports:
  - port: 3306        # Service port
    targetPort: 3306  # Container port
  type: ClusterIP     # Internal access only
```

**Why ClusterIP**: Database should only be accessible within the cluster.

### 8.3 Apply Database Deployment
```bash
kubectl apply -f db-deployment.yaml
```

### 8.4 Verify Database Deployment
```bash
# Check deployment status
kubectl get deployments

# Should show:
# NAME            READY   UP-TO-DATE   AVAILABLE   AGE
# db-deployment   1/1     1            1           1m

# Check pods
kubectl get pods -l app=db

# Should show:
# NAME                             READY   STATUS    RESTARTS   AGE
# db-deployment-xxxxxxxxx-xxxxx    1/1     Running   0          1m

# Check service
kubectl get services

# Should show:
# NAME         TYPE        CLUSTER-IP      EXTERNAL-IP   PORT(S)    AGE
# db-service   ClusterIP   10.96.xxx.xxx   <none>        3306/TCP   1m
```

### 8.5 Wait for Database to be Ready
```bash
# Wait for database pod to be ready
kubectl wait --for=condition=ready pod -l app=db --timeout=300s

# Check database logs
kubectl logs -l app=db
```

---

## Step 9: Application Deployment

### 9.1 Understanding the Application Deployment

**File**: `app-deployment.yaml`

**Key Differences from Database:**

#### Multiple Replicas
```yaml
spec:
  replicas: 2  # Two application instances for load balancing
```

#### Different Environment Variables
```yaml
env:
- name: DB_HOST
  valueFrom:
    configMapKeyRef:
      name: db-config
      key: DB_HOST  # Points to "db-service"
```

**Why `DB_HOST: db-service`**: The app connects to the database using the service name.

#### HTTP Health Checks
```yaml
livenessProbe:
  httpGet:
    path: /
    port: 80
  initialDelaySeconds: 30
  periodSeconds: 10
```

**What it does**: Makes HTTP GET request to `/` on port 80 to check if app is responding.

### 9.2 Apply Application Deployment
```bash
kubectl apply -f app-deployment.yaml
```

### 9.3 Verify Application Deployment
```bash
# Check deployment status
kubectl get deployments

# Should show both db and app deployments:
# NAME            READY   UP-TO-DATE   AVAILABLE   AGE
# app-deployment  2/2     2            2           1m
# db-deployment   1/1     1            1           5m

# Check pods
kubectl get pods -l app=app

# Should show 2 pods:
# NAME                             READY   STATUS    RESTARTS   AGE
# app-deployment-xxxxxxxxx-xxxxx   1/1     Running   0          1m
# app-deployment-xxxxxxxxx-yyyyy   1/1     Running   0          1m

# Check service
kubectl get services

# Should show:
# NAME         TYPE        CLUSTER-IP      EXTERNAL-IP   PORT(S)    AGE
# app-service  ClusterIP   10.96.xxx.xxx   <none>        80/TCP     1m
# db-service   ClusterIP   10.96.xxx.xxx   <none>        3306/TCP   5m
```

### 9.4 Wait for Application to be Ready
```bash
# Wait for all app pods to be ready
kubectl wait --for=condition=ready pod -l app=app --timeout=300s

# Check application logs
kubectl logs -l app=app
```

---

## Step 10: Proxy Deployment

### 10.1 Understanding the Proxy Deployment

**File**: `proxy-deployment.yaml`

**Key Features:**

#### LoadBalancer Service
```yaml
apiVersion: v1
kind: Service
metadata:
  name: proxy-service
spec:
  type: LoadBalancer  # External access
```

**Why LoadBalancer**: Provides external IP address for internet access.

### 10.2 Apply Proxy Deployment
```bash
kubectl apply -f proxy-deployment.yaml
```

### 10.3 Verify Proxy Deployment
```bash
# Check deployment status
kubectl get deployments

# Should show all three deployments:
# NAME              READY   UP-TO-DATE   AVAILABLE   AGE
# app-deployment    2/2     2            2           5m
# db-deployment     1/1     1            1           10m
# proxy-deployment  1/1     1            1           1m

# Check service
kubectl get services

# Should show:
# NAME           TYPE           CLUSTER-IP      EXTERNAL-IP   PORT(S)        AGE
# app-service    ClusterIP      10.96.xxx.xxx   <none>        80/TCP         5m
# db-service     ClusterIP      10.96.xxx.xxx   <none>        3306/TCP       10m
# proxy-service  LoadBalancer   10.96.xxx.xxx   localhost     80:30000/TCP   1m
```

**Note**: In Docker Desktop, LoadBalancer shows `localhost` as external IP.

### 10.4 Wait for Proxy to be Ready
```bash
# Wait for proxy pod to be ready
kubectl wait --for=condition=ready pod -l app=proxy --timeout=300s

# Check proxy logs
kubectl logs -l app=proxy
```

---

## Step 11: Deployment Execution

### 11.1 Using the Deployment Script

**File**: `deploy.sh`

This script automates the deployment process in the correct order:

```bash
#!/bin/bash

echo "Deploying AdNU CEVAS LAMP Stack to Kubernetes..."

# Apply ConfigMap first
echo "Applying ConfigMap..."
kubectl apply -f configmap.yaml

# Apply Secret
echo "Applying Secret..."
kubectl apply -f secret.yaml

# Apply PersistentVolume and PVC
echo "Applying PersistentVolume and PVC..."
kubectl apply -f persistentvolume.yaml

# Apply Database deployment and service
echo "Applying Database deployment and service..."
kubectl apply -f db-deployment.yaml

# Wait for database to be ready
echo "Waiting for database to be ready..."
kubectl wait --for=condition=ready pod -l app=db --timeout=300s

# Apply Application deployment and service
echo "Applying Application deployment and service..."
kubectl apply -f app-deployment.yaml

# Wait for application to be ready
echo "Waiting for application to be ready..."
kubectl wait --for=condition=ready pod -l app=app --timeout=300s

# Apply Proxy deployment and service
echo "Applying Proxy deployment and service..."
kubectl apply -f proxy-deployment.yaml

# Wait for proxy to be ready
echo "Waiting for proxy to be ready..."
kubectl wait --for=condition=ready pod -l app=proxy --timeout=300s

echo "Deployment completed!"
echo "Check the status with: kubectl get all"
echo "Get the external IP with: kubectl get service proxy-service"
```

### 11.2 Make Script Executable
```bash
chmod +x deploy.sh
```

### 11.3 Run Deployment
```bash
./deploy.sh
```

**Expected Output:**
```
Deploying AdNU CEVAS LAMP Stack to Kubernetes...
Applying ConfigMap...
configmap/db-config created
Applying Secret...
secret/db-secret created
Applying PersistentVolume and PVC...
persistentvolume/db-pv created
persistentvolumeclaim/db-pvc created
Applying Database deployment and service...
deployment.apps/db-deployment created
service/db-service created
Waiting for database to be ready...
pod/db-deployment-xxxxxxxxx-xxxxx condition met
Applying Application deployment and service...
deployment.apps/app-deployment created
service/app-service created
Waiting for application to be ready...
pod/app-deployment-xxxxxxxxx-xxxxx condition met
pod/app-deployment-xxxxxxxxx-yyyyy condition met
Applying Proxy deployment and service...
deployment.apps/proxy-deployment created
service/proxy-service created
Waiting for proxy to be ready...
pod/proxy-deployment-xxxxxxxxx-xxxxx condition met
Deployment completed!
Check the status with: kubectl get all
Get the external IP with: kubectl get service proxy-service
```

---

## Step 12: Verification and Testing

### 12.1 Check Overall Status
```bash
# View all resources
kubectl get all

# Expected output:
# NAME                                 READY   STATUS    RESTARTS   AGE
# pod/app-deployment-xxxxxxxxx-xxxxx   1/1     Running   0          5m
# pod/app-deployment-xxxxxxxxx-yyyyy   1/1     Running   0          5m
# pod/db-deployment-xxxxxxxxx-xxxxx    1/1     Running   0          10m
# pod/proxy-deployment-xxxxxxxxx-xxxxx 1/1     Running   0          1m
#
# NAME                    TYPE           CLUSTER-IP      EXTERNAL-IP   PORT(S)        AGE
# service/app-service     ClusterIP      10.96.xxx.xxx   <none>        80/TCP         5m
# service/db-service      ClusterIP      10.96.xxx.xxx   <none>        3306/TCP       10m
# service/proxy-service   LoadBalancer   10.96.xxx.xxx   localhost     80:30000/TCP   1m
#
# NAME                            READY   UP-TO-DATE   AVAILABLE   AGE
# deployment.apps/app-deployment  2/2     2            2           5m
# deployment.apps/db-deployment   1/1     1            1           10m
# deployment.apps/proxy-deployment 1/1     1            1           1m
```

### 12.2 Check External Access
```bash
# Get the external IP
kubectl get service proxy-service

# Expected output:
# NAME           TYPE           CLUSTER-IP      EXTERNAL-IP   PORT(S)        AGE
# proxy-service  LoadBalancer   10.96.xxx.xxx   localhost     80:30000/TCP   5m
```

### 12.3 Test Application Access
```bash
# Test using curl
curl http://localhost

# Or open in browser: http://localhost
```

### 12.4 Check Resource Details
```bash
# Check ConfigMap
kubectl get configmap db-config -o yaml

# Check Secret (won't show actual values)
kubectl get secret db-secret -o yaml

# Check PersistentVolume
kubectl get pv db-pv -o yaml

# Check PersistentVolumeClaim
kubectl get pvc db-pvc -o yaml
```

### 12.5 Check Pod Logs
```bash
# Database logs
kubectl logs -l app=db

# Application logs
kubectl logs -l app=app

# Proxy logs
kubectl logs -l app=proxy

# Follow logs in real-time
kubectl logs -l app=app -f
```

### 12.6 Check Pod Details
```bash
# Get detailed information about a pod
kubectl describe pod -l app=db

# Check pod environment variables
kubectl exec -it $(kubectl get pod -l app=db -o jsonpath='{.items[0].metadata.name}') -- env

# Access pod shell
kubectl exec -it $(kubectl get pod -l app=db -o jsonpath='{.items[0].metadata.name}') -- /bin/bash
```

---

## Troubleshooting

### Common Issues and Solutions

#### 1. Pod Stuck in Pending Status
```bash
# Check pod details
kubectl describe pod <pod-name>

# Common causes:
# - Insufficient resources
# - Image pull issues
# - PVC binding issues
```

#### 2. Image Pull Errors
```bash
# Check if secret exists
kubectl get secrets

# Recreate secret if needed
kubectl delete secret regcred
kubectl create secret docker-registry regcred \
  --docker-server=https://index.docker.io/v1/ \
  --docker-username=<USER> \
  --docker-password=<PASS> \
  --docker-email=<EMAIL>
```

#### 3. Database Connection Issues
```bash
# Check if database is running
kubectl get pods -l app=db

# Check database logs
kubectl logs -l app=db

# Test database connection from app pod
kubectl exec -it $(kubectl get pod -l app=app -o jsonpath='{.items[0].metadata.name}') -- mysql -h db-service -u cevasuser -p
```

#### 4. Service Not Accessible
```bash
# Check service endpoints
kubectl get endpoints

# Check service details
kubectl describe service <service-name>

# Test service connectivity
kubectl run test-pod --image=busybox --rm -it --restart=Never -- wget -O- http://app-service
```

#### 5. PersistentVolume Issues
```bash
# Check PV status
kubectl get pv

# Check PVC status
kubectl get pvc

# If PVC is pending, check storage class
kubectl get storageclass
```

#### 6. Resource Cleanup
```bash
# Delete all resources in namespace
kubectl delete all --all -n adnu-cevas

# Delete namespace
kubectl delete namespace adnu-cevas

# Clean up Docker registry secret
kubectl delete secret regcred
```

### Debugging Commands

#### Check Events
```bash
# View all events
kubectl get events --sort-by='.lastTimestamp'

# View events for specific resource
kubectl describe pod <pod-name>
```

#### Check Resource Usage
```bash
# Check node resources
kubectl top nodes

# Check pod resources
kubectl top pods
```

#### Network Debugging
```bash
# Test DNS resolution
kubectl run test-dns --image=busybox --rm -it --restart=Never -- nslookup db-service

# Test network connectivity
kubectl run test-connectivity --image=busybox --rm -it --restart=Never -- wget -O- http://app-service
```

---

## Cleanup

### Complete Cleanup
```bash
# Delete all resources
kubectl delete all --all -n adnu-cevas

# Delete ConfigMap and Secret
kubectl delete configmap db-config
kubectl delete secret db-secret

# Delete PersistentVolume and PVC
kubectl delete pvc db-pvc
kubectl delete pv db-pv

# Delete ServiceAccount
kubectl delete serviceaccount registry-sa

# Delete namespace
kubectl delete namespace adnu-cevas

# Delete Docker registry secret
kubectl delete secret regcred
```

### Verify Cleanup
```bash
# Check if namespace is deleted
kubectl get namespaces | grep adnu-cevas

# Should return nothing if cleanup was successful
```

---

## Summary

### What We Accomplished
1. ✅ Set up Kubernetes environment with Docker Desktop
2. ✅ Created Docker registry authentication
3. ✅ Established isolated namespace
4. ✅ Implemented all required Kubernetes resources:
   - **Deployment** (3 deployments: db, app, proxy)
   - **Service** (3 services: db-service, app-service, proxy-service)
   - **ConfigMap** (db-config for environment variables)
   - **PersistentVolume** (db-pv with hostPath storage)
   - **PersistentVolumeClaim** (db-pvc for database storage)
5. ✅ Implemented optional resources:
   - **Secret** (db-secret for sensitive data)
   - **ServiceAccount** (registry-sa for image pulling)
6. ✅ Used LoadBalancer for external access (no ClusterIP for external)
7. ✅ Used PersistentVolume for database storage
8. ✅ Used host storage class for volumes

### Architecture Overview
```
Internet → LoadBalancer (proxy-service) → Nginx Proxy → App Service → PHP App
                                                           ↓
                                                    Database Service → MariaDB (with PV)
```

### Key Learning Points
- **Namespaces** provide isolation and organization
- **Services** enable communication between pods
- **PersistentVolumes** ensure data persistence
- **ConfigMaps** and **Secrets** manage configuration
- **Health checks** ensure application reliability
- **LoadBalancer** provides external access
- **ServiceAccount** enables authentication for private images

### Next Steps
- Monitor application performance
- Set up logging and monitoring
- Implement backup strategies
- Consider scaling strategies
- Explore advanced Kubernetes features

---

## Additional Resources

### Official Documentation
- [Kubernetes Documentation](https://kubernetes.io/docs/)
- [Docker Desktop Kubernetes](https://docs.docker.com/desktop/kubernetes/)
- [kubectl Reference](https://kubernetes.io/docs/reference/kubectl/)

### Useful Commands Reference
```bash
# Basic commands
kubectl get all                    # List all resources
kubectl describe <resource> <name> # Detailed information
kubectl logs <pod-name>            # View logs
kubectl exec -it <pod-name> -- /bin/bash  # Access pod shell

# Scaling
kubectl scale deployment app-deployment --replicas=3

# Rolling updates
kubectl set image deployment/app-deployment app=c0derhin0/adnu-cevas-lamp:app-v2

# Port forwarding
kubectl port-forward service/db-service 3306:3306
```

This guide covers every aspect of deploying your LAMP stack to Kubernetes. Each step is explained in detail with the reasoning behind it, making it suitable for beginners while providing comprehensive coverage of all requirements. 