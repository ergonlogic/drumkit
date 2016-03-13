MK_DIR       ?= $(shell pwd)
SHELL        := /bin/bash
project_root ?= /vagrant

up:
	@vagrant up
rebuild:
	@vagrant destroy -f && vagrant up
make:
	@vagrant ssh -c"cd /var/www/html/d8 && sudo drush -y make /vagrant/dev.build.yml"

include $(MK_DIR)/mk/*/*.mk

