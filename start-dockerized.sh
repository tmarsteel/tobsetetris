#!/bin/bash

docker start tetris

port=$(docker container inspect --format "{{(index (index .NetworkSettings.Ports \"80/tcp\") 0).HostPort}}" tetris)
url="http://localhost:$port"
echo "$url"

# the port binding takes some time to take effect,
# this avoids a connection error screen in the browser
sleep 1s

xdg-open "$url"
