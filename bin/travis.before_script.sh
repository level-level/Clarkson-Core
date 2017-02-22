#!/bin/bash

set -e
shopt -s expand_aliases

# TODO: These should not override any existing environment variables
export WP_CORE_DIR=/tmp/wordpress
export WP_TESTS_DIR=${WP_CORE_DIR}/tests/phpunit
export PLUGIN_DIR=$(pwd)
export PLUGIN_SLUG=$(basename $(pwd) | sed 's/^wp-//')
export PHPCS_DIR=/tmp/phpcs
export PHPCS_GITHUB_SRC=squizlabs/PHP_CodeSniffer
export PHPCS_GIT_TREE=master
export PHPCS_IGNORE='tests/*,vendor/*,bin/*'
export WPCS_DIR=/tmp/wpcs
export WPCS_GITHUB_SRC=WordPress-Coding-Standards/WordPress-Coding-Standards
export WPCS_GIT_TREE=master
export DISALLOW_EXECUTE_BIT=0
export LIMIT_TRAVIS_PR_CHECK_SCOPE=files # when set to 'patches', limits reports to only lines changed; TRAVIS_PULL_REQUEST must not be 'false'
export PATH_INCLUDES=./
export WPCS_STANDARD=$(if [ -e phpcs.ruleset.xml ]; then echo phpcs.ruleset.xml; else echo WordPress-Core; fi)


# Load a .ci-env.sh to override the above environment variables
if [ -e .ci-env.sh ]; then
	source .ci-env.sh
fi

# Install the WordPress Unit Tests
if [ -e phpunit.xml ] || [ -e phpunit.xml.dist ]; then

	echo "Install Unit tests"
	bash bin/install-wp-tests.sh wordpress_test root '' localhost $WP_VERSION
	mkdir -p "${WP_CORE_DIR}/wp-content/plugins"
	echo "move to ${WP_CORE_DIR}/wp-content/plugins"
	cd ${WP_CORE_DIR}/wp-content/plugins
	#check if dir exists
	echo "move $PLUGIN_DIR to $PLUGIN_SLUG"
	mv $PLUGIN_DIR $PLUGIN_SLUG
	echo "cd $PLUGIN_SLUG"
	cd $PLUGIN_SLUG

	ln -s $(pwd) $PLUGIN_DIR
	ls -alh $(pwd)
	echo "Plugin location: $(pwd)"

	if ! command -v phpunit >/dev/null 2>&1; then
		wget -O /tmp/phpunit.phar https://phar.phpunit.de/phpunit.phar
		chmod +x /tmp/phpunit.phar
		alias phpunit='/tmp/phpunit.phar'
	fi
fi

# Install PHP_CodeSniffer and the WordPress Coding Standards
echo "Install PHP_CodeSniffer and the WordPress Coding Standards"
mkdir -p $PHPCS_DIR && curl -L https://github.com/$PHPCS_GITHUB_SRC/archive/$PHPCS_GIT_TREE.tar.gz | tar xz --strip-components=1 -C $PHPCS_DIR
mkdir -p $WPCS_DIR && curl -L https://github.com/$WPCS_GITHUB_SRC/archive/$WPCS_GIT_TREE.tar.gz | tar xz --strip-components=1 -C $WPCS_DIR
$PHPCS_DIR/scripts/phpcs --config-set installed_paths $WPCS_DIR

# Install Composer
if [ -e composer.json ]; then
	curl -s http://getcomposer.org/installer | php && php composer.phar install
fi

set +e
