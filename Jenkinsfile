pipeline {
    agent any

    environment {
        BRANCH = 'dev/0.0.0'                
        CREDENTIALS_ID = 'github_access'     
        REPO_URL = 'https://github.com/gorguimarena/omPayBack-end.git'
    }

    stages {

        stage('Checkout') {
            steps {
                echo '📦 Clonage du dépôt Git...'
                git(
                    url: env.REPO_URL,
                    branch: env.BRANCH,
                    credentialsId: env.CREDENTIALS_ID
                )
            }
        }

        stage('Build and Run Containers') {
            steps {
                script {
                    echo 'Construction des images Docker...'
                    sh 'docker compose build'

                    echo 'Lancement des conteneurs...'
                    sh 'docker compose up -d'

                    echo 'Attente du démarrage des services...'
                    sh 'sleep 30'
                }
            }
        }

        stage('Run Laravel Tests') {
            steps {
                echo 'Exécution des tests unitaires Laravel...'
                sh 'docker compose exec -T app php artisan test'
            }
        }

        stage('Stop and Clean Containers') {
            steps {
                echo 'Nettoyage des conteneurs Docker...'
                sh 'docker compose down'
            }
        }
    }

    post {

        always {
            echo 'Nettoyage complet des volumes Docker...'
            sh 'docker compose down -v'
        }

        success {
            echo 'Build et tests exécutés avec succès !'
        }

        failure {
            echo 'Erreur lors du pipeline Jenkins.'
        }
    }
}
