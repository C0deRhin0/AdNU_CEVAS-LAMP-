# Kubernetes RBAC Authentication & Authorization: Step-by-Step Guide

## Overview
This guide explains how to implement and demonstrate Kubernetes RBAC (Role-Based Access Control) using a custom regular user. It covers every step, from creating the user to verifying their permissions, and explains the purpose and value of RBAC in Kubernetes.

---

## 1. What is RBAC in Kubernetes?
**RBAC** (Role-Based Access Control) is a security mechanism in Kubernetes that lets you control who can do what within your cluster.  
- **Users** are assigned **roles**.
- **Roles** define what actions (verbs) are allowed on which resources.
- **RoleBindings** or **ClusterRoleBindings** assign those roles to users or groups.

This ensures that only authorized users can access or modify resources, improving security and operational safety.

---

## 2. Step-by-Step: Creating a Regular User with RBAC

### Step 1: Generate User Credentials
Kubernetes uses certificates for authentication.  
You’ll create a new RSA key pair and a certificate signing request (CSR) for your user.

```bash
# Generate a private key
openssl genrsa -out regular-user.key 2048

# Create a certificate signing request (CSR)
openssl req -new -key regular-user.key -out regular-user.csr -subj "/CN=regular-user/O=adnu"
```
- `regular-user.key`: The user's private key.
- `regular-user.csr`: The certificate signing request.

---

### Step 2: Create a Kubernetes CSR Resource
You need to submit your CSR to Kubernetes for approval.

1. Create a YAML file (e.g., `csr-regular-user.yaml`) in your `k8s/` folder:

```yaml
apiVersion: certificates.k8s.io/v1
kind: CertificateSigningRequest
metadata:
  name: regular-user
spec:
  groups:
  - system:authenticated
  request: <base64-encoded-csr>
  signerName: kubernetes.io/kube-apiserver-client
  usages:
  - client auth
```
- Replace `<base64-encoded-csr>` with the base64-encoded contents of your `regular-user.csr`:
  ```bash
  cat regular-user.csr | base64 | tr -d '\n'
  ```
2. Apply the CSR resource:
   ```bash
   kubectl apply -f k8s/csr-regular-user.yaml
   ```

---

### Step 3: Approve the CSR and Get the Certificate
```bash
kubectl certificate approve regular-user
kubectl get csr regular-user -o jsonpath='{.status.certificate}' | base64 --decode > regular-user.crt
```
- `regular-user.crt` is your signed certificate.

---

### Step 4: Set Up kubeconfig for the Regular User
You need to create a kubeconfig context for your new user.

```bash
kubectl config set-credentials regular-user \
  --client-certificate=regular-user.crt \
  --client-key=regular-user.key \
  --embed-certs=true

kubectl config set-context regular-user-context \
  --cluster=$(kubectl config view --minify -o jsonpath='{.clusters[0].name}') \
  --namespace=default \
  --user=regular-user
```
Switch to the new context:
```bash
kubectl config use-context regular-user-context
```

---

### Step 5: Create RBAC Roles and Bindings

1. **Create a ClusterRole** (e.g., `clusterrole-regular-user.yaml` in `k8s/`):

```yaml
apiVersion: rbac.authorization.k8s.io/v1
kind: ClusterRole
metadata:
  name: regular-user
rules:
- apiGroups: [""]
  resources: ["namespaces", "pods", "pods/log", "services"]
  verbs: ["get", "list", "watch"]
- apiGroups: ["apps"]
  resources: ["deployments", "replicasets"]
  verbs: ["get", "list", "watch"]
- apiGroups: ["rbac.authorization.k8s.io"]
  resources: ["roles", "rolebindings"]
  verbs: ["get", "list", "watch"]
```

2. **Bind the Role to the User** (e.g., `clusterrolebinding-regular-user.yaml`):

```yaml
apiVersion: rbac.authorization.k8s.io/v1
kind: ClusterRoleBinding
metadata:
  name: regular-user-binding
subjects:
- kind: User
  name: regular-user
  apiGroup: rbac.authorization.k8s.io
roleRef:
  kind: ClusterRole
  name: regular-user
  apiGroup: rbac.authorization.k8s.io
```

3. **Apply the RBAC resources:**
   ```bash
   kubectl apply -f k8s/clusterrole-regular-user.yaml
   kubectl apply -f k8s/clusterrolebinding-regular-user.yaml
   ```

---

### Step 6: Test the User’s Permissions
With your context set to `regular-user-context`, try the following:

```bash
kubectl get pods
kubectl get deployments
kubectl get services
kubectl get roles
kubectl get rolebindings
```
You should be able to **get**, **list**, and **watch** these resources, but not create, update, or delete them.

Try a forbidden action (like creating a pod):

```bash
kubectl run test-pod --image=nginx
```
You should get a "forbidden" error, confirming RBAC is working.

---

## 3. Why Do This? (Purpose of the Task)
- **Security:** RBAC ensures users only have the permissions they need—no more, no less.
- **Separation of Duties:** Developers, operators, and admins can have different access levels.
- **Auditability:** You can see who did what, and when.
- **Best Practice:** In production, never use cluster-admin for daily work. Always use least-privilege accounts.

---

## 4. Key Takeaways
- You created a regular user with a certificate for authentication.
- You defined a ClusterRole with specific permissions.
- You bound the role to your user using a ClusterRoleBinding.
- You switched kube-contexts to simulate working as that user.
- You verified that RBAC rules are enforced by Kubernetes.

---

## 5. References
- [Kubernetes RBAC Docs](https://kubernetes.io/docs/reference/access-authn-authz/rbac/)
- [Kubernetes User Management](https://kubernetes.io/docs/reference/access-authn-authz/authentication/)

---

**Congratulations!**  
You have successfully implemented and demonstrated RBAC in Kubernetes, following best practices for secure, multi-user clusters. 