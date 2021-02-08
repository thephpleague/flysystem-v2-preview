#!/usr/bin/env bash

set -e
set -x


if [ ! -f "./bin/splitsh-lite" ]; then
    bash bin/install-split.sh
fi

CURRENT_BRANCH="2.x"

function split()
{
    SHA1=`./bin/splitsh-lite --prefix=$1 --origin=origin/$CURRENT_BRANCH`
    git push $2 "$SHA1:refs/heads/$CURRENT_BRANCH" -f
}

function remote()
{
    git remote add $1 $2 || true
}

git pull origin $CURRENT_BRANCH

remote ftp git@github.com:thephpleague/flysystem-ftp.git
remote sftp git@github.com:thephpleague/flysystem-sftp.git
remote memory git@github.com:thephpleague/flysystem-memory.git
remote ziparchive git@github.com:thephpleague/flysystem-ziparchive.git
remote aws-s3-v3 git@github.com:thephpleague/flysystem-aws-s3-v3.git
remote async-aws-s3 git@github.com:thephpleague/flysystem-async-aws-s3.git
remote google-cloud-storage git@github.com:thephpleague/flysystem-google-cloud-storage.git
remote adapter-test-utilities git@github.com:thephpleague/flysystem-adapter-test-utilities.git

split 'src/Ftp' ftp
split 'src/PhpseclibV2' sftp
split 'src/InMemory' memory
split 'src/ZipArchive' ziparchive
split 'src/AwsS3V3' aws-s3-v3
split 'src/AsyncAwsS3' async-aws-s3
split 'src/GoogleCloudStorage' google-cloud-storage
split 'src/AdapterTestUtilities' adapter-test-utilities
