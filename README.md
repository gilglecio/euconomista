# Setup

- Clone repo
- Enter into /euconomista
- Run `docker-compose up -d`
- Run `docker exec -it euconomista_api composer install`
- Run `docker exec -it euconomista_api bower install`
- Run `docker exec -it euconomista_api ./db migrate`
- Run `docker exec -it euconomista_api bash` e dê permissão de escrita nas pastas `chmod -R 777 cache log`
- Access `http://localhost:3000`