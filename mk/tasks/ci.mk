ifdef BEHAT_CI_TAGS
  BEHAT_TAGS_REAL := --tags '$(BEHAT_CI_TAGS)'
else
  BEHAT_TAGS_REAL =
endif

run-behat-ci: behat
	$(behat) $(BEHAT_TAGS_REAL); \
  export RESULT=$$?; \
  ake -s ansible-playbook 2>&1 >/dev/null; \
  make -s matrix-ci; \
  exit $$RESULT
