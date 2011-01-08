#!/bin/bash

# See http://code.google.com/p/js-test-driver/ on how to write tests

SCRIPT_PATH=`dirname $0`
SCRIPT_PATH=`readlink -f $SCRIPT_PATH`
TEST_PATH=`readlink -f $SCRIPT_PATH/../tests`
JSDRIVER_BIN=`readlink -f $SCRIPT_PATH/../third_party/jstestdriver/JsTestDriver-1.2.2.jar`

cd $TEST_PATH && java -jar $JSDRIVER_BIN --verbose --tests all | \
  sed -u -e 's/^\(\[PASSED\].*\)/\o033[0;32m\1\o033[0m/' |
  sed -u -e 's/^\(\[FAILED\].*\)/\o033[0;31m\1\o033[0m/'