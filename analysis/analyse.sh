#!/bin/bash

TESTS=(All PHPMD PHPCS PHPStan PHPMetrics SonarCloud Exit)

run_test() {
	PS3="What test do you want to run?: "
	select TEST_TYPE in ${TESTS[@]}
	do
		if [[ ${TEST_TYPE} = "" ]]; then
			run_test
		fi
		if [[ ${TEST_TYPE} = "Exit" ]]; then
			exit
		fi
		break;
	done
}

run_phpmd() {
	echo -e "\n🧹 PHPMD test..."

	if ! hash ../vendor/bin/phpmd 2>/dev/null; then
		echo -e "🚨ERR: phpmd not found in vendor/bin folder."
		echo -e "INF: Install it using the composer."
		echo -e "INF: phpmd test skipped."
		return
	fi

	if [[ ! -d "phpmd" ]]; then
		mkdir phpmd
	fi

	../vendor/bin/phpmd ../src/Webiik html cleancode,codesize,design,naming,unusedcode --reportfile phpmd/index.html
	echo -e "💡INF: Look in to analysis/phpmd folder to see the PHPMD test result."
}

run_phpcs() {
	echo -e "\n🐽 PHPCS test..."

	if ! hash ../vendor/bin/phpcs 2>/dev/null; then
		echo -e "🚨ERR: phpcs not found in vendor/bin folder."
		echo -e "INF: Install it using the composer."
		echo -e "INF: phpcs test skipped."
		return
	fi

	if [[ ! -d "phpcs" ]]; then
		mkdir phpcs
	fi

	../vendor/bin/phpcs --standard=PSR2 --report-full=phpcs/report-full.txt --report-code=phpcs/report-code.txt ../src/Webiik
	echo -e "💡INF: Look in to analysis/phpcs folder to see the PHPCS test result."
}

run_phpstan() {
	echo -e "\n🔎 PHPStan test..."

	if ! hash ../vendor/bin/phpstan 2>/dev/null; then
		echo -e "🚨ERR: phpstan not found in vendor/bin folder."
		echo -e "INF: Install it using the composer."
		echo -e "INF: phpstan test skipped."
		return
	fi

	if [[ ! -d "phpstan" ]]; then
		mkdir phpstan
	fi

	../vendor/bin/phpstan analyse ../src/Webiik -l 7 --no-ansi --no-progress | awk '{$1=$1;print}' > phpstan/result.txt
	echo -e "💡INF: Look in to analysis/phpstan folder to see the PHPStan test result."
}

run_phpmetrics() {
	echo -e "\n📊 PHPmetrics test..."

	if ! hash ../vendor/bin/phpmetrics 2>/dev/null; then
		echo -e "🚨ERR: phpmetrics not found in vendor/bin folder."
		echo -e "INF: Install it using the composer."
		echo -e "INF: phpmetrics test skipped."
		return
	fi

	if [[ ! -d "phpmetrics" ]]; then
		mkdir phpmetrics
	fi

	../vendor/bin/phpmetrics --report-html=phpmetrics ../src/Webiik
	echo -e "💡INF: Look in to analysis/phpmetrics folder to see the PHPmetrics test result."
}

run_sonar_cloud() {
	echo -e "\n🐬 SonarCloud test..."

	SONAR_PROJECT_KEY=""
	SONAR_ORGANIZATION=""
	SONAR_LOGIN=""

	# Try to load SonarCloud config from file
	if [[ -f "sonar/conf.txt" ]]; then
		SONAR_CLOUD_CONFIG=()
		IFS=$'\n' read -d '' -r -a SONAR_CLOUD_CONFIG < sonar/conf.txt
		SONAR_PROJECT_KEY=${SONAR_CLOUD_CONFIG[0]}
		SONAR_ORGANIZATION=${SONAR_CLOUD_CONFIG[1]}
		SONAR_LOGIN=${SONAR_CLOUD_CONFIG[2]}
	fi

	# Do we have valid SonarCloud config?
	# If not, ask user to configure SonarCloud and store configuration for further use.
	if [[ ${SONAR_PROJECT_KEY} = "" || ${SONAR_ORGANIZATION} = "" || ${SONAR_LOGIN} = "" ]]; then
		SONAR_PROJECT_KEY=""
		while [[ ${SONAR_PROJECT_KEY} = "" ]]; do
			read -p "Enter sonar.projectKey: " SONAR_PROJECT_KEY
		done

		SONAR_ORGANIZATION=""
		while [[ ${SONAR_ORGANIZATION} = "" ]]; do
			read -p "Enter sonar.organization: " SONAR_ORGANIZATION
		done

		SONAR_LOGIN=""
		while [[ ${SONAR_LOGIN} = "" ]]; do
			read -p "Enter sonar.login: " SONAR_LOGIN
		done

		if [[ ! -d "sonar" ]]; then
			mkdir sonar
		fi

		echo ${SONAR_PROJECT_KEY} > sonar/conf.txt
		echo ${SONAR_ORGANIZATION} >> sonar/conf.txt
		echo ${SONAR_LOGIN} >> sonar/conf.txt
		echo -e "💡INF: SonarCloud configuration has been stored in analysis/sonar/conf.txt"
	fi

	# Is sonar-scanner available? If not skip test.
	if ! hash sonar-scanner 2>/dev/null; then
		echo -e "🚨ERR: sonar-scanner command not found."
		echo -e "INF: Install it from: https://docs.sonarqube.org/display/SCAN/Analyzing+with+SonarQube+Scanner"
		echo -e "INF: SonarCloud test skipped."
		return
	fi

	# Run SonarCloud scan
	sonar-scanner \
	-Dsonar.projectKey=${SONAR_PROJECT_KEY} \
	-Dsonar.organization=${SONAR_ORGANIZATION} \
	-Dsonar.projectBaseDir=../src \
	-Dsonar.sources=. \
	-Dsonar.host.url=https://sonarcloud.io \
	-Dsonar.login=${SONAR_LOGIN}

	# To make life easier, copy test report to folder analysis/sonar
	if [[ -f "../src/.scannerwork/report-task.txt" ]]; then
		cp ../src/.scannerwork/report-task.txt sonar
	fi
}

run_test

echo -e "\n🚀 Starting the test(s)..."

if [[ ${TEST_TYPE} = "PHPMD" ]]; then
	run_phpmd
fi

if [[ ${TEST_TYPE} = "PHPCS" ]]; then
	run_phpcs
fi

if [[ ${TEST_TYPE} = "PHPStan" ]]; then
	run_phpstan
fi

if [[ ${TEST_TYPE} = "PHPMetrics" ]]; then
	run_phpmetrics
fi

if [[ ${TEST_TYPE} = "SonarCloud" ]]; then
	run_sonar_cloud
fi

if [[ ${TEST_TYPE} = "All" ]]; then
	run_phpmd
	run_phpcs
	run_phpstan
	run_phpmetrics
	run_sonar_cloud
fi

echo -e "\n💡INF: Results of all performed tests can be found in analysis/{test} folders."