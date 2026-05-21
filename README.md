# Donut Shop Management System

A full-stack **DevOps CI/CD** project for managing a donut shop: view products, place orders, view orders, and delete orders. Built with PHP, MySQL, Docker, Kubernetes, Jenkins, and Prometheus/Grafana-compatible monitoring.

## Tech Stack

| Layer | Technology |
|-------|------------|
| Frontend | HTML, CSS |
| Backend | PHP (mysqli) |
| Database | MySQL 8.0 |
| Containerization | Docker |
| Orchestration | Kubernetes |
| CI/CD | Jenkins |
| Monitoring | Prometheus + Grafana |

## Project Structure

```
donut-shop/
├── index.php              # Main application
├── db.php                 # MySQL connection (env vars)
├── style.css              # Responsive UI styles
├── Dockerfile             # PHP 8.2 Apache image
├── docker-compose.yml     # Local dev stack
├── Jenkinsfile            # CI/CD pipeline
├── README.md
├── sql/
│   └── init.sql           # Schema + sample data
├── k8s/
│   ├── mysql-deployment.yaml
│   ├── mysql-service.yaml
│   ├── mysql-init-configmap.yaml
│   ├── app-deployment.yaml
│   └── app-service.yaml
├── monitoring/
│   └── prometheus-config.yaml
└── screenshots/
```

## Features

- View donut menu with product cards
- Place orders with customer name, product, and quantity
- View all orders in a sortable table
- Delete orders with confirmation
- Persistent MySQL storage
- Containerized and Kubernetes-ready deployment

## Prerequisites

- [Docker](https://docs.docker.com/get-docker/) & Docker Compose
- [kubectl](https://kubernetes.io/docs/tasks/tools/) (for K8s)
- [Jenkins](https://www.jenkins.io/) (for CI/CD)
- Kubernetes cluster (Minikube, Kind, or cloud)

## Quick Start with Docker Compose

1. **Clone the repository**

   ```bash
   git clone https://github.com/YOUR_USERNAME/donut-shop.git
   cd donut-shop
   ```

2. **Start all services**

   ```bash
   docker compose up -d --build
   ```

3. **Access the application**

   | Service | URL |
   |---------|-----|
   | Donut Shop App | http://localhost:8080 |
   | phpMyAdmin | http://localhost:8081 |

4. **Environment variables (app service)**

   | Variable | Default |
   |----------|---------|
   | HOST | mysql |
   | USER | donut_user |
   | PASSWORD | donut_pass |
   | DATABASE | donutdb |

5. **Stop services**

   ```bash
   docker compose down
   ```

   Remove volumes (reset database):

   ```bash
   docker compose down -v
   ```

## Docker Commands

```bash
# Build image only
docker build -t donut-shop:latest .

# Run app container (requires MySQL network)
docker run -d -p 8080:80 \
  -e HOST=mysql-host \
  -e USER=donut_user \
  -e PASSWORD=donut_pass \
  -e DATABASE=donutdb \
  donut-shop:latest

# Push to Docker Hub
docker tag donut-shop:latest YOUR_USERNAME/donut-shop:latest
docker login
docker push YOUR_USERNAME/donut-shop:latest
```

## Kubernetes Deployment

1. **Update Docker Hub username** in `k8s/app-deployment.yaml`:

   ```yaml
   image: YOUR_DOCKERHUB_USERNAME/donut-shop:latest
   ```

2. **Apply manifests in order**

   ```bash
   kubectl apply -f k8s/mysql-init-configmap.yaml
   kubectl apply -f k8s/mysql-deployment.yaml
   kubectl apply -f k8s/mysql-service.yaml
   kubectl apply -f k8s/app-deployment.yaml
   kubectl apply -f k8s/app-service.yaml
   ```

3. **Verify deployment**

   ```bash
   kubectl get pods
   kubectl get services
   kubectl rollout status deployment/donut-app-deployment
   ```

4. **Access the application**

   NodePort service exposes the app on port **30080**:

   ```bash
   minikube service donut-app-service --url
   # Or: http://<node-ip>:30080
   ```

5. **Database connection in K8s**

   The app uses `HOST=mysql-service` (ClusterIP DNS name) to connect to MySQL inside the cluster.

## Jenkins CI/CD Setup

### Required Jenkins Plugins

- Pipeline
- Git
- Docker Pipeline
- Kubernetes CLI
- Credentials Binding

### Jenkins Credentials

Create these credentials in Jenkins (**Manage Jenkins → Credentials**):

| ID | Type | Purpose |
|----|------|---------|
| `github-credentials` | Username/Password or SSH | Git clone |
| `dockerhub-credentials` | Username/Password | Docker Hub login |
| `dockerhub-username` | Secret text | Docker Hub username |

### Pipeline Configuration

1. Create a **Pipeline** job in Jenkins.
2. Set **Pipeline script from SCM** → Git → your repository URL.
3. Set **Script Path** to `Jenkinsfile`.
4. Update `GIT_REPO` and `YOUR_DOCKERHUB_USERNAME` in the Jenkinsfile.

### Pipeline Stages

1. **Code Fetch** – Clone GitHub repository
2. **Docker Image Creation** – Build Docker image
3. **DockerHub Push** – Push image to Docker Hub
4. **Kubernetes Deployment** – Apply K8s manifests with `kubectl`
5. **Monitoring** – Apply Prometheus ConfigMap

### Jenkins Agent Requirements

- Docker installed and Jenkins user in `docker` group
- `kubectl` configured with cluster kubeconfig
- Network access to Docker Hub and Kubernetes API

## Prometheus & Grafana Setup

1. **Apply Prometheus configuration**

   ```bash
   kubectl create configmap prometheus-config \
     --from-file=prometheus.yml=monitoring/prometheus-config.yaml
   ```

2. **Install Prometheus** (Helm example)

   ```bash
   helm repo add prometheus-community https://prometheus-community.github.io/helm-charts
   helm install prometheus prometheus-community/prometheus \
     -f monitoring/prometheus-values.yaml
   ```

3. **Install Grafana**

   ```bash
   helm install grafana grafana/grafana
   kubectl port-forward svc/grafana 3000:80
   ```

4. **Access Grafana**

   - URL: http://localhost:3000
   - Default user: `admin` (retrieve password from secret)

   ```bash
   kubectl get secret grafana -o jsonpath="{.data.admin-password}" | base64 -d
   ```

5. **Add Prometheus data source** in Grafana: `http://prometheus-server:80`

6. **Optional**: Deploy `mysql-exporter` and `node-exporter` for metrics referenced in `prometheus-config.yaml`.

## Database Schema

**products** – Donut menu items  
**orders** – Customer orders with product reference, quantity, total, and status

Sample data is loaded from `sql/init.sql` (Docker Compose) or `k8s/mysql-init-configmap.yaml` (Kubernetes).

## Troubleshooting

| Issue | Solution |
|-------|----------|
| App cannot connect to DB | Verify MySQL is healthy; check HOST env matches service name (`mysql` or `mysql-service`) |
| Empty product list | Ensure `init.sql` ran; check MySQL logs: `docker logs donut-shop-mysql` |
| K8s ImagePullBackOff | Replace `YOUR_DOCKERHUB_USERNAME` and push image to Docker Hub |
| Jenkins sed fails on Windows agent | Use Linux-based Jenkins agent for pipeline |

## License

Educational / academic project for DevOps coursework.

## Author

Donut Shop Management System – DevOps CI/CD Project
