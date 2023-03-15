<!-- References:
https://www.twilio.com/blog/get-started-docker-laravel
https://laravel-for-newbie.kejyun.com/en/advanced/scheduling/docker/
https://github.com/mohammadain/laravel-docker-cron/blob/master/Dockerfile -->

<!-- Improved compatibility of back to top link: See: https://github.com/othneildrew/Best-README-Template/pull/73 -->

<a name="readme-top"></a>

<!-- PROJECT LOGO -->
<br />
<div align="center">
  <a href="https://github.com/rconfig/rconfig6-core">
    <img src="https://www.rconfig.com/images/new_logos/blue_logos/artwork_blue_horizontal_Artboard_1_96px.png" alt="Logo" >
  </a>

  <h3 align="center">rConfig v6 Core</h3>

  <p align="center">
    rConfig v6 Core is a free, open source, community edition of rConfig v6. It is a fully functional version of rConfig v6, with all the core features of rConfig v6 Professional, but with some limitations. Check out our features list <a href="https://www.rconfig.com/features"><strong>www.rconfig.com/features</strong></a> to learn more.
    <br />
    <br />
    <a href="https://www.rconfig.com/docs"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="#intro">Intro</a>
    ·
    <a href="#setup">Installation</a>
    ·
    <a href="#usage">Usage</a>
    ·
    <a href="#contributing">Contributing</a>
    ·
    <a href="#license">License</a>
    ·
    <a href="#support">Support</a>
  </p>

[![Tests](https://github.com/eliashaeussler/typo3-badges/actions/workflows/tests.yaml/badge.svg)](https://github.com/eliashaeussler/typo3-badges/actions/workflows/tests.yaml)
[![License](https://img.shields.io/github/license/eliashaeussler/typo3-badges)](LICENSE) [![Made with Node](https://img.shields.io/badge/dynamic/json?label=node&query=%24.engines%5B%22node%22%5D&url=https%3A%2F%2Fraw.githubusercontent.com%2FMichaelCurrin%2Fbadge-generator%2Fmaster%2Fpackage.json)](https://nodejs.org 'Go to Node.js homepage')
[![PHP Version Require](http://poser.pugx.org/pugx/badge-poser/require/php)](https://packagist.org/packages/pugx/badge-poser)

 <img src="https://img.shields.io/badge/-Vue3-4FC08D?logo=vue.js&logoColor=white&style=flat"/>
 <img src="https://img.shields.io/badge/-Laravel-FF2D20?logo=laravel&logoColor=white&style=flat"/>
 <!-- <img src="https://img.shields.io/badge/-Tailwind%20CSS-06B6D4?logo=tailwind-css&logoColor=white&style=flat"/> -->
 <img src="https://img.shields.io/badge/-ViteJs-6e37a0?logo=vite&logoColor=white&style=flat"/>
 <img src="https://img.shields.io/badge/-PatternFly-004285?logo=Ghost&logoColor=white&style=flat"/>
 <img src="https://img.shields.io/badge/-mySQL-4479A1?logo=mysql&logoColor=white&style=flat"/>

</div>

<!-- Intro -->

<a name="intro"></a>

## Intro

rConfig v6 is an enterprise grade Network Configuration Management (NCM) software package with superior NCM features and capabilities to help you easily manage configurations on large and small heterogenous networks. rConfig v6 is our flagship professional version of rConfig aimed at high value networks and business operations. rConfig v6 runs natively on many variants of Linux. Within this repo, is the code base for rConfig v6 Core, and a set of scripts to help you get started with rConfig v6 Core.

If you are looking for rConfig V6 professional, please visit `https://www.rconfig.com/`.

Supported OS

-   Rocky Linux 8/9+
-   RHEL Linux 8/9+
-   CentOS Linux 8/9+
-   Ubuntu 20.04+
-   Docker (Linux)

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- Installation -->

<a name="setup"></a>

## Installation

We have made it super easy to get started with rConfig v6. Follow the steps below to get started.

### Prerequisites

Setup a server with one of the supported OS's listed above. We recommend using a fresh install of the OS. If you are using a server that has been in production, we recommend you backup the server before proceeding.

You will need to install the following software on your server:
Git 2.25+
PHP 8.1+
Composer 2.4+
Apache 2.4+
MySQL 5.7+ or MariaDB 10.5+
nodejs 14.17+
Supervisor 4.2+

We have conveniently provided scripts to help you install the required software. Head over to [https://www.rconfig.com/docs/6.9/getstarted/os-setup](https://www.rconfig.com/docs/6.9/getstarted/os-setup) to find the correct script for your OS.

### Setup Steps

1. Login as root
2. Clone the repo

```sh
git clone https://github.com/rconfig/rconfig6-core.git
```

3. Change directory to the repo

```sh
cd rConfig6CoreDev
```

4. Create the .env file

```sh
cp .env.example .env
```

5. Edit the .env file and update the following variables:

```sh
APP_URL="https://SERVER.DOMAIN.LOCAL"
APP_DIR_PATH=/var/www/html/DIRNAME
DB_HOST=DBHOST
DB_PORT=DBPORT
DB_DATABASE=DBNAME
DB_USERNAME=DBUSER
DB_PASSWORD=DBPASS
```

6. Install the required PHP packages

```sh
export COMPOSER_ALLOW_SUPERUSER=1
composer self-update --2
yes | composer install --no-dev
```

7. Run the installation script

```sh
php artisan install
```

If you area asked 'Add a cron entry for task scheduling?', type `yes` and press enter.

The output form the above should look like this:

```sh



                                                   APPLICATION INSTALL.


  Packages discovery ...................................................................................................................... 5ms DONE

  command key:generate .................................................................................................................... 2ms DONE
  command migrate ......................................................................................................................... 2ms DONE
  command passport:install ................................................................................................................ 7ms DONE
  exec    sed -i -e s+PWD+$PWD+g $PWD/horizon_supervisor.ini .............................................................................. 9ms DONE
  exec    if [ -f /etc/supervisord.d/horizon_supervisor.ini ]; then unlink /etc/supervisord.d/horizon_supervisor.ini; fi .................. 4ms DONE
  exec    sudo ln -s $PWD/horizon_supervisor.ini /etc/supervisord.d/horizon_supervisor.ini ............................................... 14ms DONE
  exec    systemctl restart supervisord ............................................................................................... 3,274ms DONE
  exec    sed -i -e s+PWD+$PWD+g /etc/httpd/conf.d/rconfig-vhost.conf ..................................................................... 5ms DONE
  exec    if [ -f /etc/httpd/conf.d/rconfig-vhost.conf ]; then unlink /etc/httpd/conf.d/rconfig-vhost.conf; fi ............................ 3ms DONE
  exec    sudo ln -s $PWD/rconfig-vhost.conf /etc/httpd/conf.d/rconfig-vhost.conf ........................................................ 14ms DONE
  exec    systemctl restart httpd ..................................................................................................... 1,139ms DONE
  exec    chown -R apache:apache $PWD ................................................................................................... 118ms DONE
  command rconfig:clear-all
No config updates to processes

> Illuminate\Foundation\ComposerScripts::postAutoloadDump
Generated optimized autoload files containing 6948 classes
........................................................................................................... 5,187ms DONE
  command rconfig:sync-tasks .............................................................................................................. 2ms DONE
  script  cache .......................................................................................................................... 27ms DONE
  script  build ...................................................................................................................... 21,478ms DONE
  command clinotify ....................................................................................................................... 1ms DONE
  command about .......................................................................................................................... 86ms DONE

  No assets for publishing .........................................................................................................................

  Add a cron entry for task scheduling? (yes/no) [no]
❯ yes

   INFO  Entry was added [* * * * * cd /var/www/html/rConfig6CoreDev && php artisan schedule:run >> /dev/null 2>&1].

   INFO  Install done!

```

8. Update apache config file for correct server name

```sh
sudo nano /etc/httpd/conf.d/rconfig-vhost.conf
```

Update the `ServerName` to match your server's domain name.

```sh
ServerName YourServerName.domain.local
ServerAlias YourServerName.domain.local
```

9. Restart apache

```sh
sudo systemctl restart httpd
```

10. Open your browser and navigate to your server's domain name. You should see the rConfig login page.

Check out our docs `www.rconfig.com/docs` to learn more.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- CONTRIBUTING -->

<a name="contributing"></a>

## Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**. We are fairly strict on code quality and style. Please follow the best practices. You should also have a strong working knowledge of PHP, Laravel, and VueJS.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement".
Don't forget to give the project a star! Thanks again!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request for approval to merge into the `develop` branch

#### How to run tests

1. Create a new database for testing
2. Copy the `.env.example` file to `.env.testing`
3. php artisan key:generate --env=testing
4. change the `APP_ENV` variable in the `.env.testing` file to `testing`
5. Update the `.env.testing` file with the correct database credentials
6. Run the tests with `php artisan test`

Front end development requires `npm install --include=dev`, and `npm run dev` to compile the assets with vite.

    If you get a 'connect ENETUNREACH on npm' command, export the following env var
    export NODE_OPTIONS="--dns-result-order=ipv4first"
    You can add this to the ~./bashrc file

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- LICENSE -->

<a name="license"></a>

## License

This code base for this repository's code is distributed under License from rConfig. See `LICENSE.txt` for more information. rConfig v6 Professional is excluded from this license and repository.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- https://github.com/othneildrew/Best-README-Template/blob/master/README.md -->

<a name="support"></a>

## Support

Although we provide this code free and open source, rConfig v6 core is based best effort support basis. You may open issues in the issue section here at github. We will try to address issues in a timely manner, but without guarantees. For prompt support and business critical environments, you should take out a subscription for rCOnfig v6 Professional. rConfig Professional subscribers should open a ticket via our normal support channels.

## Acknowledgments

Inspiration, code snippets, etc.

-   [Laravel](https://www.laravel.com)
-   [vuejs](https://vuejs.org/)
-   [patternfly v4](https://www.patternfly.org/v4/)

See composer.json and package.json for a full list of dependencies, and their licenses.
