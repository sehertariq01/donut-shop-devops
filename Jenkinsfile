// Donut Shop Management System - CI/CD Pipeline
pipeline {
    agent any

    environment {
        GIT_REPO = 'https://github.com/sehertariq01/donut-shop-devops.git'
        GIT_BRANCH = 'master'

        DOCKER_IMAGE = 'donut-shop'
        DOCKER_TAG = "${BUILD_NUMBER}"
        DOCKERHUB_USERNAME = 'sehar123'

        K8S_NAMESPACE = 'default'
        K8S_MANIFEST_PATH = 'k8s'
    }

    options {
        buildDiscarder(logRotator(numToKeepStr: '10'))
        timeout(time: 30, unit: 'MINUTES')
        timestamps()
    }

    stages {
        stage('Code Fetch Stage') {
            steps {
                echo '=== Code Fetch Stage ==='
                git branch: "${GIT_BRANCH}",
                    url: "${GIT_REPO}"
                sh 'ls -la'
            }
        }

        stage('Docker Image Creation Stage') {
            steps {
                echo '=== Docker Image Creation Stage ==='
                sh """
                    docker build -t ${DOCKERHUB_USERNAME}/${DOCKER_IMAGE}:${DOCKER_TAG} .
                    docker tag ${DOCKERHUB_USERNAME}/${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKERHUB_USERNAME}/${DOCKER_IMAGE}:latest
                """
            }
        }

        stage('DockerHub Push Stage') {
            steps {
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
        }

        stage('Kubernetes Deployment Stage') {
            steps {
                echo '=== Kubernetes Deployment Stage ==='
                sh """
                    sed -i 's|image: .*donut-shop:.*|image: ${DOCKERHUB_USERNAME}/${DOCKER_IMAGE}:${DOCKER_TAG}|g' ${K8S_MANIFEST_PATH}/app-deployment.yaml

                    kubectl apply -f ${K8S_MANIFEST_PATH}/mysql-init-configmap.yaml
                    kubectl apply -f ${K8S_MANIFEST_PATH}/mysql-pvc.yaml
                    kubectl apply -f ${K8S_MANIFEST_PATH}/mysql-deployment.yaml
                    kubectl apply -f ${K8S_MANIFEST_PATH}/mysql-service.yaml

                    kubectl apply -f ${K8S_MANIFEST_PATH}/app-deployment.yaml
                    kubectl apply -f ${K8S_MANIFEST_PATH}/app-service.yaml
                    kubectl apply -f ${K8S_MANIFEST_PATH}/hpa.yaml

                    kubectl rollout status deployment/mysql-deployment -n ${K8S_NAMESPACE} --timeout=120s
                    kubectl rollout status deployment/donut-app-deployment -n ${K8S_NAMESPACE} --timeout=120s

                    kubectl get pods -n ${K8S_NAMESPACE}
                    kubectl get svc -n ${K8S_NAMESPACE}
                    kubectl get pvc -n ${K8S_NAMESPACE}
                    kubectl get hpa -n ${K8S_NAMESPACE}
                """
            }
        }

        stage('Prometheus/Grafana Stage') {
            steps {
                echo '=== Prometheus/Grafana Stage ==='
                sh """
                    echo 'Prometheus and Grafana are installed using Helm kube-prometheus-stack.'
                    echo 'Check monitoring namespace:'
                    kubectl get pods -n monitoring || true
                    helm list -A || true
                """
            }
        }
    }

    post {
        success {
            echo 'Pipeline completed successfully!'
            echo 'Application Service: donut-app-service'
            echo 'Application NodePort: 30080'
            echo 'Grafana is available through port-forward on port 3000.'
        }

        failure {
            echo 'Pipeline failed. Check Jenkins console output.'
        }

        always {
            echo 'Workspace cleanup skipped.'
        }
    }
}