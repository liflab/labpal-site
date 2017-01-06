#! /bin/bash
rsync --exclude-from rsync-excludes.txt --delete -az public_html/ ../../labpal/docs