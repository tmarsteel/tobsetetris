#!/bin/bash

set -x

docker container rm tetris

set -e
cd "$( dirname -- "${BASH_SOURCE[0]}")"

IMAGE_ID=$(docker build -q .)
docker container create --name tetris -P "$IMAGE_ID"
