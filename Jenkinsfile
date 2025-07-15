pipeline {
  agent any

  stages {
    stage('Checkout & Clean') {
      steps {
        cleanWs()
        checkout scm
      }
    }

    stage('Copy .env Files') {
      steps {
        withCredentials([
          file(credentialsId: 'public-complaint-app-client-dev-env', variable: 'CLIENT_DEV_ENV'),
          file(credentialsId: 'public-complaint-app-client-prod-env', variable: 'CLIENT_PROD_ENV'),
          file(credentialsId: 'public-complaint-app-server-dev-env', variable: 'SERVER_DEV_ENV'),
        ]) {
          sh '''
            cp "$CLIENT_DEV_ENV" client/.env.development
            cp "$CLIENT_PROD_ENV" client/.env.production
            cp "$SERVER_DEV_ENV" server/.env.development
            cp server/.env.development server/.env
          '''
        }
      }
    }

    stage('Start Dev Containers') {
      steps {
        sh '''
          docker system prune -af --volumes || true
          docker compose -f docker-compose.development.yml down --volumes --remove-orphans || true
          docker compose -f docker-compose.development.yml up -d --build
        '''
      }
    }

    stage('Run Server Tests') {
      steps {
        sh '''
          until docker compose -f docker-compose.development.yml exec server sh -c "nc -z postgres 5432"; do
            sleep 1
          done

          docker compose -f docker-compose.development.yml exec server sh -c "
            php artisan migrate:fresh --seed &&
            php artisan test 
          "
        '''
      }
    }

    stage('Build Production Images') {
      steps {
        sh 'docker compose -f docker-compose.production.yml build'
      }
    }

    stage('Push Docker Images') {
      steps {
        withCredentials([
          usernamePassword(
            credentialsId: 'dockerhub',
            usernameVariable: 'DOCKER_USER',
            passwordVariable: 'DOCKER_PASS',
          ),
        ]) {
          sh '''
            echo "$DOCKER_PASS" | docker login -u "$DOCKER_USER" --password-stdin
            docker compose -f docker-compose.production.yml push
          '''
        }
      }
    }
  }

  post {
    always {
      steps {
        sh '''
          docker compose -f docker-compose.development.yml down --volumes --remove-orphans || true
          docker system prune -af --volumes || true
        '''
        cleanWs()
      }
    }
  }
}
