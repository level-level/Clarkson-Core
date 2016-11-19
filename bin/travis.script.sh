#!/bin/bash

set -e

if [ "$TRAVIS_PULL_REQUEST" != 'false' ] && ( [ "$LIMIT_TRAVIS_PR_CHECK_SCOPE" == 'files' ] || [ "$LIMIT_TRAVIS_PR_CHECK_SCOPE" == 'patches' ] ); then
	git diff --diff-filter=AM --no-prefix --unified=0 $TRAVIS_BRANCH...$TRAVIS_COMMIT -- $PATH_INCLUDES | php bin/parse-diff-ranges.php  > /tmp/checked-files
else
	find $PATH_INCLUDES -type f | grep -v -E "^./(bin|\.git)/" | sed 's:^\.//*::' > /tmp/checked-files
fi

echo "LIMIT_TRAVIS_PR_CHECK_SCOPE: $LIMIT_TRAVIS_PR_CHECK_SCOPE"
echo "TRAVIS_BRANCH: $TRAVIS_BRANCH"
echo "Files to check:"
cat /tmp/checked-files
echo

function remove_diff_range {
	sed 's/:[0-9][0-9]*-[0-9][0-9]*$//' | sort | uniq
}
function filter_php_files {
	 grep -E '\.php(:|$)'
}

# Run PHP syntax check
for php_file in $( cat /tmp/checked-files | remove_diff_range | filter_php_files ); do
	php -lf "$php_file"
done


# Run PHP_CodeSniffer
echo "## PHP_CodeSniffer"
if ! cat /tmp/checked-files | remove_diff_range | filter_php_files | xargs --no-run-if-empty $PHPCS_DIR/scripts/phpcs -s --report-emacs=/tmp/phpcs-report --standard=$WPCS_STANDARD $(if [ -n "$PHPCS_IGNORE" ]; then echo --ignore=$PHPCS_IGNORE; fi); then
    cat /tmp/phpcs-report
    exit 1
fi

# Run PHPUnit tests
if [ -e phpunit.xml ] || [ -e phpunit.xml.dist ]; then
	phpunit $( if [ -e .coveralls.yml ]; then echo --coverage-clover build/logs/clover.xml; fi )
fi