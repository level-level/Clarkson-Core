workflow  "Generate documentation" {
  resolves = [
    "Publish documentation"
  ]
  on = "push"
}

action "Publish documentation" {
  needs = ["Generate wp-hookdoc", "Generate phpdoc"]
  uses = "maxheld83/ghpages@v0.2.1"
  env = {
    BUILD_DIR = "out/"
  }
  secrets = ["GH_PAT"]
}
action "Generate phpdoc" {
  needs = ["Is master"]
  uses = "docker://uniplug/apigen"
  args = "apigen generate -d out/phpdoc -s lib -s wordpress-objects --access-levels public -o"
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