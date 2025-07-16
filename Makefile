# Kubernetes LAMP Stack Management
# Usage: make <target>

# Deploy all components
deploy:
	kubectl apply -f ./k8s/configmap.yaml
	kubectl apply -f ./k8s/secret.yaml
	kubectl apply -f ./k8s/persistentvolume.yaml
	kubectl apply -f ./k8s/db-deployment.yaml
	kubectl wait --for=condition=ready pod -l app=db --timeout=300s
	kubectl apply -f ./k8s/app-deployment.yaml
	kubectl wait --for=condition=ready pod -l app=app --timeout=300s
	kubectl apply -f ./k8s/proxy-deployment.yaml
	kubectl wait --for=condition=ready pod -l app=proxy --timeout=300s

# Delete all deployments and services
delete:
	kubectl delete -f ./k8s/proxy-deployment.yaml -f ./k8s/app-deployment.yaml -f ./k8s/db-deployment.yaml

# Scale application replicas (default: 2)
scale ?= 2
scale-app:
	kubectl scale deployment app-deployment --replicas=$(scale)

# Get status of all resources
status:
	kubectl get all -l app=app -l app=db -l app=proxy

# Get pod logs
logs-app:
	kubectl logs -l app=app --tail=50
logs-db:
	kubectl logs -l app=db --tail=50
logs-proxy:
	kubectl logs -l app=proxy --tail=50

# Get external service IP
external-ip:
	kubectl get service proxy-service

# Port forward to services
port-forward-app:
	kubectl port-forward service/app-service 9090:80
port-forward-db:
	kubectl port-forward service/db-service 3306:3306

# Describe resources
describe-pods:
	kubectl describe pods -l app=app -l app=db -l app=proxy
describe-services:
	kubectl describe services -l app=app -l app=db -l app=proxy

# Restart deployments
restart-app:
	kubectl rollout restart deployment app-deployment
restart-db:
	kubectl rollout restart deployment db-deployment
restart-proxy:
	kubectl rollout restart deployment proxy-deployment

# Check rollout status
rollout-status:
	kubectl rollout status deployment app-deployment
	kubectl rollout status deployment db-deployment
	kubectl rollout status deployment proxy-deployment

# Get resource usage
top:
	kubectl top pods -l app=app -l app=db -l app=proxy

# Force clean up persistent volumes (use when PV/PVC stuck in terminating)
force-clean:
	kubectl delete -f ./k8s/proxy-deployment.yaml -f ./k8s/app-deployment.yaml -f ./k8s/db-deployment.yaml 

	kubectl patch pvc db-pvc -p '{"metadata":{"finalizers":null}}' --type=merge
	kubectl delete pvc db-pvc --grace-period=0 --force --ignore-not-found
	kubectl patch pv db-pv -p '{"metadata":{"finalizers":null}}' --type=merge
	kubectl delete pv db-pv --grace-period=0 --force --ignore-not-found

	kubectl delete -f ./k8s/persistentvolume.yaml --ignore-not-found
	kubectl delete -f ./k8s/configmap.yaml --ignore-not-found
	kubectl delete -f ./k8s/secret.yaml --ignore-not-found

# Quick restart (delete + deploy)
restart: delete deploy

# Show all resources
all:
	kubectl get all

# Show events
events:
	kubectl get events --sort-by=.metadata.creationTimestamp

# Show persistent volumes
pvc:
	kubectl get pvc,pv

# Show configmaps and secrets
config:
	kubectl get configmap,secret	

namespace:
	kubectl config view --minify --output 'jsonpath={..namespace}'


