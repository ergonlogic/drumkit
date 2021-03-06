---
title: Hugo Docs Project Type
weight: 30

---

## Embedded Hugo Documentation Site

Drumkit includes all the necessary components to embed a Hugo documentation site in your main project.

### Initialization
At the root of the project, you can install a complete hugo site using `make init-project-hugo-docs`.
If you want to use a specific version of hugo, you can speficy it using the variable `hugo_RELEASE`

This will:
- Download a local copy of hugo (inside the `.mk/.local/bin` folder)
- Create a new `docs` folder that contains the skeleton of a hugo site
- Install the `learn` theme for hugo
- Add the hugo theme as a git submodule
- Initialize the necessary information for deploying the hugo site to Gitlab Pages from a Gitlab CI process 

To see the new hugo site on a local development machine, `cd` into the `docs` folder and `hugo serve`. This will start a local server on port 1313 that will automatically reload as you make changes to the files.

### Editing

The automatically generated starting point is in `docs/content/_index.md`. To publish this page, you need to remove the `draft: true` line from the title section.

For guidance on using hugo to layout your docs site, refer to [hugo documentation](https://gohugo.io/getting-started/usage/)

### Local testing

Initialization of the hugo docs site with drumkit includes the addition of a `gitlab-ci.yml` file at the root of the project.

This file is used by gitlab-runner to trigger the CI tests.

**If you cloned using the development script, you will need to update the URL of the .mk submodule manually for these tests to run successfully.**

Edit the submodule information in `.gitmodules` at the root of the containing project. Change the URL of .mk to: https://gitlab.com/consensus.enterprises/drumkit.git


### Deployment

The deployment to Gitlab Pages is managed automatically by the `.gitlab-ci.yml` file.

At the bottom of the file, under `pages`, the `publish` stage will run `hugo` in the docs folder, which generates a set of static HTML files in the `public` folder, which is then made available through Gitlab pages.


The address at Gitlab Pages will be `http://<GITLAB_GROUP>.gitlab.io/<GITLAB_PROJECT_NAME>/`

To set up your Gitlab Pages, you need to update the configuration in `docs/config.yaml`, which is set to "http://mygroup.gitlab.io/myproject". 

