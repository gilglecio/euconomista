# Setup

- Clone repo
- Enter into /euconomista
- Run `docker-compose up -d`
- Run `docker exec -it euconomista_api composer install`
- Run `docker exec -it euconomista_api bower install`
- Run `docker exec -it euconomista_api ./db migrate`
- Access `http://localhost:3000`