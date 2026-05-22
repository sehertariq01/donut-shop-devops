# Donut Shop Management System

A full-stack **DevOps CI/CD** project for managing a donut shop. The system allows users to view products, place orders, view orders, and delete orders. The project demonstrates a complete DevOps workflow using **GitHub, Jenkins, Docker, DockerHub, Kubernetes, Helm, Prometheus, Grafana, HPA, and MySQL persistent storage**.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML, CSS |
| Backend | PHP mysqli |
| Database | MySQL 8.0 |
| Containerization | Docker |
| Local Development | Docker Compose |
| CI/CD | Jenkins |
| Image Registry | DockerHub |
| Orchestration | Kubernetes / Minikube |
| Storage | PersistentVolumeClaim PVC |
| Autoscaling | Horizontal Pod Autoscaler HPA |
| Monitoring | Prometheus + Grafana |
| Monitoring Installation | Helm kube-prometheus-stack |

---

## Project Structure

```text
donut-shop-devops/
├── index.php
├── db.php
├── style.css
├── Dockerfile
├── docker-compose.yml
├── Jenkinsfile
├── README.md
│
├── sql/
│   └── init.sql
│
├── k8s/
│   ├── app-deployment.yaml
│   ├── app-service.yaml
│   ├── mysql-deployment.yaml
│   ├── mysql-init-configmap.yaml
│   ├── mysql-pvc.yaml
│   ├── mysql-service.yaml
│   └── hpa.yaml
│
└── screenshots/
```

---

## Features

- View donut menu with product cards
- Place orders with customer name, product, and quantity
- View all orders
- Delete orders
- MySQL database integration
- Dockerized PHP application
- Kubernetes deployment
- Persistent MySQL storage using PVC
- HPA-based autoscaling
- Jenkins CI/CD pipeline
- DockerHub image push
- GitHub webhook automation
- Prometheus and Grafana monitoring using Helm
- Grafana monitoring dashboards for Kubernetes   and application metrics

---

## Prerequisites

- AWS EC2 Ubuntu machine
- Docker
- Docker Compose
- Git
- Jenkins
- kubectl
- Minikube
- Helm
- DockerHub account
- GitHub repository

---

## Docker Compose Local Test

Start application locally:

```bash
docker compose up -d --build
```

Check containers:

```bash
docker ps
```

Access services:

| Service | URL |
|---|---|
| Donut Shop App | `http://EC2_PUBLIC_IP:8080` |
| phpMyAdmin | `http://EC2_PUBLIC_IP:8081` |

Stop:

```bash
docker compose down
```

Remove volumes:

```bash
docker compose down -v
```

---

## Docker Image Build And Push

Build image:

```bash
docker build -t sehar123/donut-shop:latest .
```

Login DockerHub:

```bash
docker login
```

Push image:

```bash
docker push sehar123/donut-shop:latest
```

---

## Kubernetes Deployment

Start Minikube:

```bash
minikube start --driver=docker
```

Check node:

```bash
kubectl get nodes
```

Apply Kubernetes manifests:

```bash
kubectl apply -f k8s/mysql-init-configmap.yaml
kubectl apply -f k8s/mysql-pvc.yaml
kubectl apply -f k8s/mysql-deployment.yaml
kubectl apply -f k8s/mysql-service.yaml

kubectl apply -f k8s/app-deployment.yaml
kubectl apply -f k8s/app-service.yaml

kubectl apply -f k8s/hpa.yaml
```

Verify:

```bash
kubectl get pods
kubectl get svc
kubectl get pv
kubectl get pvc
kubectl get hpa
```

Access app:

```bash
kubectl port-forward service/donut-app-service 8082:80 --address 0.0.0.0
```

Open:

```text
http://EC2_PUBLIC_IP:8082
```

---

## MySQL Persistent Storage

MySQL uses a PersistentVolumeClaim:

```text
mysql-pvc.yaml
```

Purpose:

```text
If MySQL pod is deleted or restarted, database data remains saved.
```

Test persistence:

```bash
kubectl delete pod <mysql-pod-name>
```

Check pod recreated:

```bash
kubectl get pods
```

Verify data still exists by opening the application or entering MySQL shell.

---

## HPA Autoscaling

The project includes HPA:

```text
hpa.yaml
```

Check HPA:

```bash
kubectl get hpa
```

Generate load:

```bash
while true; do curl http://localhost:8082; done
```

Watch scaling:

```bash
kubectl get hpa -w
kubectl get pods -w
```

HPA scales the application pods based on CPU utilization.

---

## Jenkins CI/CD Setup

### Required Jenkins Plugins

- Pipeline
- Git
- Docker Pipeline
- Credentials Binding
- Kubernetes CLI

### Jenkins Credentials

Create DockerHub credentials:

| ID | Type | Purpose |
|---|---|---|
| `dockerhub-credentials` | Username with password | DockerHub login |

### Pipeline Configuration

1. Create a Pipeline job in Jenkins.
2. Select:
   ```text
   Pipeline script from SCM
   ```
3. SCM:
   ```text
   Git
   ```
4. Repository URL:
   ```text
   https://github.com/sehertariq01/donut-shop-devops
   ```
5. Branch:
   ```text
   */master
   ```
6. Script Path:
   ```text
   Jenkinsfile
   ```
7. Enable:
   ```text
   GitHub hook trigger for GITScm polling
   ```

---

## Jenkins Pipeline Stages

1. Code Fetch Stage
2. Docker Image Creation Stage
3. DockerHub Push Stage
4. Kubernetes Deployment Stage
5. Prometheus/Grafana Stage

---

## GitHub Webhook Automation

GitHub webhook automatically triggers Jenkins pipeline after every push.

Webhook URL:

```text
http://EC2_PUBLIC_IP:9090/github-webhook/
```

Content Type:

```text
application/json
```

Event:

```text
Just the push event
```

Workflow:

```text
GitHub Push → Jenkins Pipeline → Docker Build → DockerHub Push → Kubernetes Deployment
```

Test webhook:

```bash
git add .
git commit -m "Webhook test"
git push
```

Expected:

```text
Jenkins pipeline starts automatically.
```

---

## Prometheus And Grafana Monitoring

Prometheus and Grafana are installed using Helm.

### Add Helm Repository

```bash
helm repo add prometheus-community https://prometheus-community.github.io/helm-charts
helm repo update
```

### Create Namespace

```bash
kubectl create namespace monitoring
```

### Install Monitoring Stack

```bash
helm install monitoring prometheus-community/kube-prometheus-stack --namespace monitoring
```

### Verify Monitoring Pods

```bash
kubectl get pods -n monitoring
```

### Access Grafana

```bash
kubectl port-forward -n monitoring service/monitoring-grafana 3000:80 --address 0.0.0.0
```

Open:

```text
http://EC2_PUBLIC_IP:3000
```

### Grafana Credentials

Username:

```text
admin
```

Password:

```bash
kubectl get secret monitoring-grafana -n monitoring -o jsonpath="{.data.admin-password}" | base64 -d && echo
```

### Dashboard Used

```text
Kubernetes / Views / Global
```

The dashboard monitors:

- Donut application CPU usage
- Donut application memory usage
- MySQL deployment metrics
- Kubernetes pod status
- Node resource usage
- HPA scaling metrics
- Container resource utilization


## Useful Verification Commands

Check pods:

```bash
kubectl get pods
```
Check deployments:

```bash
kubectl get deployments
```

Check services:

```bash
kubectl get svc
```

Check PV/PVC:

```bash
kubectl get pv
kubectl get pvc
```

Check HPA:

```bash
kubectl get hpa
```

Check monitoring:

```bash
kubectl get pods -n monitoring
```

Check Helm releases:

```bash
helm list -A
```

Check live metrics:

```bash
kubectl top pods
kubectl top nodes
```

If metrics API is not available:

```bash
minikube addons enable metrics-server
```

---

## After EC2 Restart

Start Docker:

```bash
sudo systemctl start docker
```

Start Jenkins:

```bash
sudo systemctl start jenkins
```

Start Minikube:

```bash
minikube start --driver=docker
```

Check pods:

```bash
kubectl get pods
kubectl get pods -n monitoring
```

Access app:

```bash
kubectl port-forward service/donut-app-service 8082:80 --address 0.0.0.0
```

Access Grafana:

```bash
kubectl port-forward -n monitoring service/monitoring-grafana 3000:80 --address 0.0.0.0
```

---

## Troubleshooting

| Issue | Solution |
|---|---|
| App cannot connect to DB | Verify MySQL pod and mysql-service |
| ImagePullBackOff | Verify DockerHub image and push |
| Jenkins cannot access Docker | Add Jenkins to docker group |
| Jenkins cannot access Kubernetes | Configure kubeconfig for Jenkins |
| Grafana login failed | Retrieve password from Kubernetes secret |
| Metrics API not available | Run `minikube addons enable metrics-server` |
| HPA not scaling | Verify metrics-server is running |
| Webhook not triggering | Check GitHub webhook and Jenkins trigger |

---

## Final Architecture

```text
Developer
   ↓
GitHub Repository
   ↓
GitHub Webhook
   ↓
Jenkins Pipeline
   ↓
Docker Image Build
   ↓
DockerHub Push
   ↓
Kubernetes Deployment
   ↓
MySQL with PVC
   ↓
HPA Autoscaling
   ↓
Prometheus Metrics Collection
   ↓
Grafana Dashboard Monitoring
```

---

## Final Output

The project successfully demonstrates:

- Cloud-based Jenkins setup on AWS EC2
- GitHub webhook integration
- Docker image creation
- DockerHub image push
- Kubernetes application deployment
- MySQL database deployment
- Persistent database storage using PVC
- Application autoscaling using HPA
- Prometheus and Grafana monitoring using Helm
- Custom Grafana dashboard for application and database metrics

---

## License

Educational academic project for DevOps coursework.

---

## Author

Donut Shop Management System – DevOps CI/CD Project
