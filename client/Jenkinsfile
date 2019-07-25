pipeline {

    agent { label "!master"} //run on slaves only

    environment {
        DOCKER_REGISTRY = 'registry.service.opg.digital'
        IMAGE = 'opguk/digi-deps-frontend'
    }

    stages {

        stage('lint') {
            steps {
                echo 'PHP_CodeSniffer PSR-2'
                sh '''
                    docker run \
                    --rm \
                    --user `id -u` \
                    --volume $(pwd):/app \
                    registry.service.opg.digital/opguk/phpcs \
                        --standard=PSR2 \
                        --report=checkstyle \
                        --report-file=checkstyle.xml \
                        --runtime-set ignore_warnings_on_exit true \
                        --runtime-set ignore_errors_on_exit true \
                        src/
                '''
            }
            post {
                always {
                    checkstyle pattern: 'checkstyle.xml'
                }
            }
        }

        stage('build composer deps') {
            steps {
                sh '''
                    docker run \
                    --env GIT_COMMITTER_NAME=Jenkinsfile \
                    --env GIT_COMMITTER_EMAIL=Jenkinsfile@local \
                    --rm \
                    --user `id -u` \
                    --volume $(pwd):/app \
                    composer/composer:php5 \
                        install
                '''
            }
        }

        stage('node deps') {
            steps {
                sh '''
                    docker run \
                    --rm \
                    --user `id -u` \
                    --volume $(pwd):/data \
                    digitallyseamless/nodejs-bower-grunt \
                        npm install
                '''
            }
        }


        stage('gulp tasks') {
            steps {
                sh '''
                    docker run \
                    --rm \
                    --user `id -u` \
                    --volume $(pwd):/app \
                    registry.service.opg.digital/opguk/digi-deps-frontend \
                        bash -c 'npm rebuild node-sass; gulp'
                '''
            }
        }

        stage('unit tests') {
            steps {
                sh '''
                docker run \
                --rm \
                --user `id -u` \
                --volume $(pwd):/app \
                registry.service.opg.digital/opguk/phpunit \
                    -c tests/phpunit/phpunit.xml \
                    --log-junit unit_results.xml
                '''
            }
            post {
                always {
                    junit 'unit_results.xml'
                }
            }
        }

        stage('unit tests coverage') {
            steps {
                echo 'PHPUnit with coverage'
                sh '''
                    docker run \
                    --rm \
                    --user `id -u` \
                    --volume $(pwd):/app \
                    registry.service.opg.digital/opguk/phpunit \
                        -c tests/phpunit/phpunit.xml \
                        --coverage-clover tests/coverage/clover.xml \
                        --coverage-html tests/coverage/
                    echo 'Fixing coverage file paths due to running in container'
                    sed -i "s#<file name=\\"/app#<file name=\\"#" tests/coverage/clover.xml
                '''
                step([
                    $class: 'CloverPublisher',
                    cloverReportDir: 'tests/coverage/',
                    cloverReportFileName: 'clover.xml'
                ])
            }
        }

        stage('build') {
            steps {
                sh '''
                    # Empty for now
                    #docker-compose down
                    #docker-compose build
                '''
            }
        }

        stage('functional tests') {
            steps {
                sh '''
                    # Empty for no functional tests
                    #docker-compose run --rm --user `id -u` tests
                    #docker-compose down
                '''
            }
            // post {
            //     always {
            //         junit 'module/Application/tests/functional/functional_results.xml'
            //     }
            // }
        }

        stage('create the tag') {
            steps {
                script {
                    if (env.BRANCH_NAME != "master") {
                        env.STAGEARG = "--stage ci"
                    } else {
                        // this can change to `-dev` tags when we switch over.
                        env.STAGEARG = "--stage master"
                    }
                }
                script {
                    sh '''
                        virtualenv venv
                        . venv/bin/activate
                        pip install git+https://github.com/ministryofjustice/semvertag.git@1.1.0
                        git fetch --tags
                        semvertag bump patch $STAGEARG >> semvertag.txt
                        NEWTAG=$(cat semvertag.txt); semvertag tag ${NEWTAG}
                    '''
                    env.NEWTAG = readFile('semvertag.txt').trim()
                    currentBuild.description = "${IMAGE}:${NEWTAG}"
                }
                echo "Storing ${env.NEWTAG}"
                archiveArtifacts artifacts: 'semvertag.txt'
            }
        }

        stage('build image') {
            steps {
                sh '''
                  docker build . -t ${DOCKER_REGISTRY}/${IMAGE}:${NEWTAG}
                '''
            }
        }

        stage('push image') {
            steps {
                sh '''
                  docker push ${DOCKER_REGISTRY}/${IMAGE}:${NEWTAG}
                '''
            }
        }

        stage('trigger downstream build') {
            when {
                branch 'master'
            }
            steps {
                build job: '/digideps/opg-digi-deps-docker/master', propagate: false, wait: false
            }
        }
    }

    post {
        // Always cleanup docker containers, especially for aborted jobs.
        always {
            sh '''
              docker-compose down --remove-orphans
            '''
        }
    }

}
