#!/usr/bin/env bash

set -x

TESTS_BASE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}" )" >/dev/null && pwd)"
BASE_DIR="$TESTS_BASE_DIR/../"

cd $TESTS_BASE_DIR

FAILED=0

# a fixed terminal width, so that the output does not depend on the terminal the tests run in
export COLUMNS=120
# no forced colors either
unset FORCE_COLOR

rm -r composer.lock vendor || true
composer install

for TEST_DIR in fatal-config fatal-config-chained fatal-config-json fatal-config-verbose only-option rules-summary typo3-extension typo3-typoscript typo3-xml typo3-yaml
do
    set +x
    echo
    echo "############################################################"
    echo "#"
    echo "# Running test in $TEST_DIR/"
    echo "#"
    echo "############################################################"
    echo
    set -x

    # remove output from a previous run, if any
    [[ -f $TEST_DIR/output.txt ]] && rm $TEST_DIR/output.txt
    [[ -f $TEST_DIR/error-output.txt ]] && rm $TEST_DIR/error-output.txt
    [[ -d $TEST_DIR/result/ ]] && rm -rf $TEST_DIR/result/
    # copy over our fixture to the path that Fractor will run in
    cp -r $TEST_DIR/fixtures/ $TEST_DIR/result/

    if [[ -f $TEST_DIR/cli-options.txt ]]; then
        CLI_OPTIONS=$(cat $TEST_DIR/cli-options.txt)
    else
        CLI_OPTIONS=""
    fi

    # env.txt holds KEY=value pairs without spaces or quotes, separated by whitespace
    if [[ -f $TEST_DIR/env.txt ]]; then
        ENV_VARS=$(cat $TEST_DIR/env.txt)
    else
        ENV_VARS=""
    fi

    env $ENV_VARS ./vendor/bin/fractor process -c $TESTS_BASE_DIR/$TEST_DIR/fractor.php $CLI_OPTIONS > $TEST_DIR/output.txt 2> $TEST_DIR/error-output.txt
    EXIT_CODE=$?
    cat $TEST_DIR/error-output.txt >&2

    if [[ -f $TEST_DIR/expected-exit-code.txt ]]; then
        EXPECTED_EXIT_CODE=$(cat $TEST_DIR/expected-exit-code.txt)
    else
        EXPECTED_EXIT_CODE=0
    fi

    set +x
    echo
    echo "############################################################"
    echo "# Comparing Fractor exit code against expected exit code"
    echo
    set -x

    [[ $EXPECTED_EXIT_CODE =~ ^[0-9]+$ && $EXIT_CODE == "$EXPECTED_EXIT_CODE" ]] || FAILED=1

    set +x
    echo
    echo "############################################################"
    echo "# Comparing Fractor result against expected result"
    echo
    set -x

    # TODO remove -b once we keep the output format when re-writing the file
    diff -rub --color=auto $TEST_DIR/expected-result/ $TEST_DIR/result/ || FAILED=1

    set +x
    echo
    echo "############################################################"
    echo "# Comparing Fractor CLI output against expected output"
    echo
    set -x

    diff -u --ignore-trailing-space --color=auto $TEST_DIR/expected-output.txt $TEST_DIR/output.txt || FAILED=1

    if [[ -f $TEST_DIR/expected-error-output.txt ]]; then
        set +x
        echo
        echo "############################################################"
        echo "# Comparing Fractor CLI error output against expected error output"
        echo
        set -x

        # compared exactly: the error block is padded to the fixed terminal width
        diff -u --color=auto $TEST_DIR/expected-error-output.txt $TEST_DIR/error-output.txt || FAILED=1
    fi
done

exit $FAILED
