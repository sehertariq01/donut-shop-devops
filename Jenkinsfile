// Donut Shop Management System - CI/CD Pipeline
// Stages: Code Fetch, Docker Build, DockerHub Push, K8s Deploy, Monitoring

pipeline {
    agent any

    environment {
        // GitHub repository URL - update with your repo
        GIT_REPO = 'https://github.com/YOUR_USERNAME/donut-shop.git'
        GIT_BRANCH = 'main'

        // Docker image configuration
        DOCKER_IMAGE = 'donut-shop'
        DOCKER_TAG = "${BUILD_NUMBER}"
        DOCKERHUB_USERNAME = credentials('dockerhub-username')
        DOCKERHUB_CREDENTIALS = credentials('dockerhub-credentials')

        // Kubernetes namespace
        K8S_NAMESPACE = 'default'
        K8S_MANIFEST_PATH = 'k8s'
    }

    options {
        buildDiscarder(logRotator(numToKeepStr: '10'))
        timeout(time: 30, unit: 'MINUTES')
        timestamps()
    }

    stages {
        // Stage 1: Fetch source code from GitHub
        stage('Code Fetch') {
            steps {
                script {
                    echo '=== Code Fetch Stage ==='
                    echo "Cloning repository: ${GIT_REPO}"
                }
                git branch: "${GIT_BRANCH}",
                    url: "${GIT_REPO}",
                    credentialsId: 'github-credentials'
                sh 'ls -la'
                echo 'Source code fetched successfully.'
            }
        }

        // Stage 2: Build Docker image
        stage('Docker Image Creation') {
            steps {
                script {
                    echo '=== Docker Image Creation Stage ==='
                sh """
                    docker build -t ${DOCKERHUB_USERNAME}/${DOCKER_IMAGE}:${DOCKER_TAG} .
                    docker tag ${DOCKERHUB_USERNAME}/${DOCKER_IMAGE}:${DOCKER_TAG} \
                        ${DOCKERHUB_USERNAME}/${DOCKER_IMAGE}:latest
                """
                }
                echo "Docker image built: ${DOCKERHUB_USERNAME}/${DOCKER_IMAGE}:${DOCKER_TAG}"
            }
        }

        // Stage 3: Push image to Docker Hub
        stage('DockerHub Push') {
            steps {
                script {
                    echo '=== DockerHub Push Stage ==='
                    withCredentials([usernamePassword(
                        credentialsId: 'dockerhub-credentials',
                        usernameVariable: 'DOCKER_USER',
                        passwordVariable: 'DOCKER_PASS'
                    )]) {
                        sh """
                            echo "\${DOCKER_PASS}" | docker login -u "\${DOCKER_USER}" --password-stdin
                            docker push ${DOCKERHUB_USERNAME}/${DOCKER_IMAGE}:${DOCKER_TAG}
                            docker push ${DOCKERHUB_USERNAME}/${DOCKER_IMAGE}:latest
                            docker logout
                        """
                    }
                }
                echo 'Image pushed to Docker Hub successfully.'
            }
        }

        // Stage 4: Deploy to Kubernetes
        stage('Kubernetes Deployment') {
            steps {
                script {
                    echo '=== Kubernetes Deployment Stage ==='
                // Update deployment image to newly built tag
                sh """
                    sed -i 's|YOUR_DOCKERHUB_USERNAME/donut-shop:latest|${DOCKERHUB_USERNAME}/${DOCKER_IMAGE}:${DOCKER_TAG}|g' \
                        ${K8S_MANIFEST_PATH}/app-deployment.yaml

                    kubectl apply -f ${K8S_MANIFEST_PATH}/mysql-init-configmap.yaml
                    kubectl apply -f ${K8S_MANIFEST_PATH}/mysql-deployment.yaml
                    kubectl apply -f ${K8S_MANIFEST_PATH}/mysql-service.yaml
                    kubectl apply -f ${K8S_MANIFEST_PATH}/app-deployment.yaml
                    kubectl apply -f ${K8S_MANIFEST_PATH}/app-service.yaml

                    kubectl rollout status deployment/mysql-deployment -n ${K8S_NAMESPACE} --timeout=120s
                    kubectl rollout status deployment/donut-app-deployment -n ${K8S_NAMESPACE} --timeout=120s
                """
                }
                sh 'kubectl get pods,services -n default'
                echo 'Kubernetes deployment completed.'
            }
        }

        // Stage 5: Deploy/update monitoring configuration
        stage('Monitoring') {
            steps {
                script {
                    echo '=== Monitoring Stage ==='
                sh """
                    kubectl create configmap prometheus-config \
                        --from-file=prometheus.yml=monitoring/prometheus-config.yaml \
                        -n ${K8S_NAMESPACE} --dry-run=client -o yaml | kubectl apply -f - || true

                    echo 'Prometheus config applied. Ensure Prometheus/Grafana stack is installed.'
                    kubectl get configmap prometheus-config -n ${K8S_NAMESPACE} 2>/dev/null || echo 'Prometheus stack not yet deployed - apply monitoring/prometheus-config.yaml manually'
                """
                }
                echo 'Monitoring configuration stage completed.'
            }
        }
    }

    post {
        success {
            echo 'Pipeline completed successfully!'
            echo "Application URL (NodePort): http://<node-ip>:30080"
        }
        failure {
            echo 'Pipeline failed. Check Jenkins console output for details.'
        }
        always {
            cleanWs()
        }
    }
}
