# Website Crawler

Crawling webpages recursively

Crawls the HTML content of up to 100 (configurable) pages of a given site with a breadth-first approach. The downloaded
pages will be stored as
HTML in a folder in the file system.
The crawler is able to work with up to 50 parallel processes. The number of processes can be passed as an env variable.

## Requirements

1. Make tool
2. Docker

## Usage

### Dev

1. `make build-dev`
2. Setup env vars (optional, see default values below):
    * MESSENGER_TRANSPORT_DSN=amqp://rabbitmq:rabbitmq@mq:5672/%2f/messages
    * APP_STORAGE_TARGET_DIR=/app/var/
    * APP_LINKS_MAX=100
    * WORKER_NUMPROCS=5
3. `make init-dev`
4. `make run-dev <WEBSITE_URL>`
5. `make test`

### Prod

1. `make build-prod`
2. Setup env vars (optional, see default values below):
    * MESSENGER_TRANSPORT_DSN=amqp://rabbitmq:rabbitmq@mq:5672/%2f/messages
    * APP_STORAGE_TARGET_DIR=/app/var/
    * APP_LINKS_MAX=100
    * WORKER_NUMPROCS=5
3. `docker run --rm -it webcrw-app:latest bin/console scrape:website:run <WEBSITE_URL>`
