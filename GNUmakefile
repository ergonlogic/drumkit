# Find the path to the originally called Makefile.
orig-mk       = $(CURDIR)/$(word $(words $(MAKEFILE_LIST)),$(MAKEFILE_LIST))
# Set the absolute path to the directory in which `make` was called.
MK_DIR       := $(shell dirname $(call orig-mk))
PROJECT_ROOT ?= $(CURDIR)
FILES_DIR    ?= $(MK_DIR)/files
SCRIPTS_DIR    ?= $(MK_DIR)/scripts
SHELL        := /bin/bash

default: help

include $(MK_DIR)/lib/gmsl/gmsl
include $(MK_DIR)/mk/drumkit.mk
include $(MK_DIR)/mk/tools.mk
include $(MK_DIR)/mk/projects/*.mk
include $(MK_DIR)/mk/tasks/*.mk

ifeq ($(MK_D_EXISTS), 1)
  ifeq ($(MK_D_NONEMPTY), 1)
    include $(MK_D)/*.mk
  endif
endif

# Include the self-documentation makefile after MK_D gets included
# (above), so that files from MK_D get scanned for inline help messages:
include $(MK_DIR)/mk/selfdoc.mk
