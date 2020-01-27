#!/bin/bash

# Gather required parameters from user
if [[ -z "$1" ]]; then
	echo -e "ERR: Missing required parameters"
	echo -e "Usage: spush-tag [tag] [repo-dir(s)]"
	exit
fi

dirs=("$@")
currentDir=${PWD}

# Remove repos
if [[ ! -d ~/webiik-repos/ ]]; then
	mkdir ~/webiik-repos/
fi

if [[ "$#" == 1 ]]; then
	# Tag all repos

	# List all directories in src/Webiik
	for dir in src/Webiik/*
	do
		# Remove the trailing "/"
		dir=${dir%*/}

		# Get everything after the final "/"
		dir=${dir##*/}

		# Remove existing split repo dir
		if [[ -d ~/webiik-repos/${dir} ]]; then
			sudo rm -r ~/webiik-repos/${dir}
		fi

		mkdir ~/webiik-repos/${dir}

		cd ~/webiik-repos/${dir}

		git init --bare

		cd ${currentDir}

		git subtree split --prefix=src/Webiik/${dir} -b ${dir}

		git push ~/webiik-repos/${dir} ${dir}:master

		cd ~/webiik-repos/${dir}

		repo=$(echo ${dir} | perl -pe 's/([a-z]|[0-9])([A-Z])/\1-\2/g')
		repo=$(echo ${repo} | tr '[:upper:]' '[:lower:]')

		git remote add origin https://github.com/webiik/${repo}.git

		git push origin :refs/tags/${1}

		git push origin master --force

		cd ${currentDir}

		git branch -D ${dir}

		git subtree push --prefix=src/Webiik/${dir} https://github.com/webiik/${repo}.git master --squash
	done
fi

if [[ "$#" > 1 ]]; then
	# Tag one or more repos
	for dir in "${dirs[@]:1}"
	do
		# Check if dir exists
		if [[ ! -d "src/Webiik/${dir}" ]]; then
			echo -e "🚨ERR: src/Webiik/${dir} doesn't exist, skipped."
			continue
		fi

		# Remove existing split repo dir
		if [[ -d ~/webiik-repos/${dir} ]]; then
			sudo rm -r ~/webiik-repos/${dir}
		fi

		mkdir ~/webiik-repos/${dir}

		cd ~/webiik-repos/${dir}

		git init --bare

		cd ${currentDir}

		git subtree split --prefix=src/Webiik/${dir} -b ${dir}

		git push ~/webiik-repos/${dir} ${dir}:master

		cd ~/webiik-repos/${dir}

		repo=$(echo ${dir} | perl -pe 's/([a-z]|[0-9])([A-Z])/\1-\2/g')
		repo=$(echo ${repo} | tr '[:upper:]' '[:lower:]')

		git remote add origin https://github.com/webiik/${repo}.git

		git push origin :refs/tags/${1}

		git push origin master --force

		cd ${currentDir}

		git branch -D ${dir}

		git subtree push --prefix=src/Webiik/${dir} https://github.com/webiik/${repo}.git master --squash
	done
fi