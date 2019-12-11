lando_NAME         ?= lando
lando_RELEASE      ?= v3.0.0-rc.22
lando_FILENAME     ?= $(lando_NAME)-$(lando_RELEASE).deb
lando_DOWNLOAD_URL ?= https://github.com/lando/$(lando_NAME)/releases/download/$(lando_RELEASE)/$(lando_FILENAME)

lando: docker
	@echo "Installing Lando."
	@wget $(lando_DOWNLOAD_URL)
	@sudo dpkg -i $(lando_FILENAME)