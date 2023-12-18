# TTetris
An HTML5 Tetris game with online scoreboard and cheating protection.

## Installing

This project is built on ancient libraries and tools (PHP 5 with mysqli, apache server)
and thus probably impossible to operate safely on a public-facing box.  
Hence this project has a Docker setup so you can run it on your machine easily:

To build the image:
```bash
./set-up-dockerized.sh
```

This starts the container with a random port binding and opens the
localhost-url in your browser:
```bash
./start-dockerized.sh
```

Should you really want to host this publicly let the Dockerfile be your guide on
the things you need to prepare.

### Dependencies

docker
