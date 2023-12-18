#!/bin/bash

docker start tetris

port=$(docker container inspect --format "{{(index (index .NetworkSettings.Ports \"80/tcp\") 0).HostPort}}" tetris)
url="http://localhost:$port"
echo "$url"

xdg-open "$url"
