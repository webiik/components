<p align="left">
<img src="https://img.shields.io/packagist/l/webiik/webiik.svg"/>
<img src="https://img.shields.io/badge/dependencies-5-brightgreen.svg"/>
</p>

Analyse
=======
The Analyse provides convenient way to analyse code quality of Webiik. It uses [PHPCS][1], [PHPMD][2], [PHPMetrics][3], [PHPStan][4] and [SonarCloud][5].

Usage
-----
1. Run `analyse.sh` in its directory:
   ```bash
   bash analyse.sh
   ```
2. Results will be available in the same directory in folders of individual tests.

Resources
---------
* [Webiik framework][1]
* [Report issues][2]

[1]: https://github.com/webiik/webiik
[2]: https://github.com/webiik/webiik-components/issues
[3]: https://github.com/squizlabs/PHP_CodeSniffer
[4]: https://phpmd.org
[5]: https://www.phpmetrics.org
[6]: https://github.com/phpstan/phpstan
[7]: https://sonarcloud.io/about