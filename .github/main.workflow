workflow  "Generate documentation" {
  resolves = [
    "Publish documentation"
  ]
  on = "push"
}

action "Publish documentation" {
  needs = ["Copy index"]
  uses = "maxheld83/ghpages@v0.2.1"
  env = {
    BUILD_DIR = "out/"
  }
  secrets = ["GH_PAT"]
}
action "Copy index" {
  needs = ["Generate wp-hookdoc", "Generate phpdoc"]
  uses = "actions/bin/sh@master"
  args = ["cp public/index.html out/index.html"]
}
action "Generate phpdoc" {
  needs = ["Is master"]
  uses = "docker://phpdoc/phpdoc"
  args = "project:run -d ./lib,./wordpress-objects -f clarkson-core.php --visibility=public -t out/phpdoc"
}
action "Generate wp-hookdoc" {
  needs = ["NPM install"]
  uses = "actions/npm@master"
  args = "run doc"
}
action "NPM install" {
  needs = ["Is master"]
  uses = "actions/npm@master"
  args = "ci"
}
action "Is master" {
  uses = "actions/bin/filter@master"
  args = "branch master"
}