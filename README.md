# Coding Task

Installation
------------
Clone the repository and install dependencies with Composer:

    $ git clone git@github.com:va5ja/codingtask.git
    $ composer install

Start docker compose:

    $ docker-compose up

If you don't have Composer installed locally, you can also run:

    $ docker-compose exec php composer install

Run migrations to create the database:

    $ docker-compose exec php php bin/console doctrine:migrations:migrate

Run fixtures to fill the database with demo data:

    $ docker-compose exec php php bin/console doctrine:fixtures:load

Basic documentation
-------------------
There's a [swagger file](https://github.com/va5ja/codingtask/blob/main/swagger.yaml) available with request/response schemas and property descriptions.

Example graph
-------------
Create a new graph:

    $ curl -X POST "http://localhost:8001/api/graphs" -H  "accept: application/json" -H  "X-AUTH-TOKEN: token123" -H  "Content-Type: application/json" -d "{\"name\":\"New graph\"}"

Add nodes and edges:

    $ curl --location --request POST 'http://localhost:8001/api/graphs/5V6JYWJM689YN8FAXG016TYNPK/edges' \
            --header 'X-AUTH-TOKEN: token123' \
            --header 'Content-Type: application/json' \
            --data-raw '[{
            "fromNode": {"name": "A"},
            "toNode": {"name": "B"}
            },
            {
            "fromNode": {"name": "B"},
            "toNode": {"name": "C"}
            },
            {
            "fromNode": {"name": "C"},
            "toNode": {"name": "D"}
            },
            {
            "fromNode": {"name": "A"},
            "toNode": {"name": "E"}
            },
            {
            "fromNode": {"name": "E"},
            "toNode": {"name": "F"}
            },
            {
            "fromNode": {"name": "F"},
            "toNode": {"name": "G"}
            },
            {
            "fromNode": {"name": "G"},
            "toNode": {"name": "H"}
            },
            {
            "fromNode": {"name": "H"},
            "toNode": {"name": "D"}
            }]'

Get the shortest path:

    $ curl --location --request POST 'http://localhost:8001/api/graphs/5V6JYWJM689YN8FAXG016TYNPK/shortest-path' \
            --header 'X-AUTH-TOKEN: token123' \
            --header 'Content-Type: application/json' \
            --data-raw '{
            "fromNode": "A",
            "toNode": "D"
            }'

Check the status of the shortest path (and download the CSV file):

    $ curl --location --request GET 'http://localhost:8001/api/graphs/5V6JYWJM689YN8FAXG016TYNPK/shortest-path/4VQRXD7PWK9WPAA05NAQDEHQ25' \
            --header 'X-AUTH-TOKEN: token123'
