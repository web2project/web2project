# web2Project Containeraziation

This is a containeraziation for [web2Project](https://github.com/web2project/web2project). It builds upon the [php:8.4-apache](https://hub.docker.com/_/php/) docker image.

## How to use

### Podman

In [Podman](https://podman.io/) you can create a pod for the by using `podman pod create --name w2p -p [port]:80 --security-opt apparmor=unconfined`. 

Then build the image using the available Containerfile in this repo. The image will contain all the requirements and permissions for web2Project to run `podman build -f w2p.Containerfile -t w2p .`. 

To create a mysql database you can use the [official docker image](https://hub.docker.com/_/mysql/) and add it to the pod: `podman run --replace --name w2p-mysql --pod w2p -e MYSQL_ROOT_PASSWORD=[proot_assword] -e MYSQL_DATABASE=w2p -e MYSQL_USER=w2p -e MYSQL_PASSWORD=[db_password] -d docker.io/library/mysql:8`

Finally run the w2p container inside the pod `podman run --replace -d --pod w2p -v w2p:/var/www/html/  --name w2p w2p`

In the initial system configuration page you should use 127.0.0.1:3306 as the host, localhost:3306 does not work for some reason.

Please be aware however that at the time of writing apparmor has an intermittent [issue on handling permissions](https://github.com/containers/podman/issues/24142) you may be able to start it with no problem but killing the container can have issues.

### Docker

I have not created a compose file for this project but the image works fine with `docker build -f w2p.Containerfile -t w2p .`

TODO:
- [ ] Docker compose file
- [ ] Podman quadlet