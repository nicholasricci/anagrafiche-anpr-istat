#!/bin/bash

docker run --rm -i -t -u $(id -u):$(id -g) --name=anagrafiche-anpr-istat \
  -v $PWD:/var/www/html \
  anagrafiche-anpr-istat bash