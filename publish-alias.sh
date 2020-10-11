#!/bin/bash

set -e

if [ "$#" -ne 2 ]; then
    echo "Expected two arguments, version to tag (v2.3.5) and github oauth token"
    exit -1
fi

TAG=$1
OAUTH_KEY=$2
REPO="acmailer/acmailer-alias"

# Get latest commit in master, in plain text
LATEST_COMMIT=$(curl -H "Accept: application/vnd.github.sha" -X GET https://api.github.com/repos/${REPO}/commits/main)

# Create new tag and a ref to the tag in the alias repo
curl -u acelaya:${OAUTH_KEY} \
    -H "Content-Type: application/json" \
    --data "{ \"tag\": \"${TAG}\", \"message\": \"${TAG}\", \"object\": \"${LATEST_COMMIT}\", \"type\": \"commit\" }" \
    -X POST https://api.github.com/repos/${REPO}/git/tags
curl -u acelaya:${OAUTH_KEY} \
    -H "Content-Type: application/json" \
    --data "{ \"ref\": \"refs/tags/${TAG}\", \"sha\": \"${LATEST_COMMIT}\" }" \
    -X POST https://api.github.com/repos/${REPO}/git/refs
