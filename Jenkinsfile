pipeline {
    agent any

    environnement {
        BRANCH = 'main'
    }

    stages {
        stage('Clone project ') {
            steps {
                git(
                    url: 'https://github.com/gorguimarena/omPayBack-end.git',
                    branch: env('BRANCH'),
                    credentialsId: 'github_access'
                )
            }
        }

        stage('Clone Nice') {
            steps {
                echo 'Clone nice !'
            }
        }
    }
}
