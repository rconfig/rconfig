<!-- References:
https://www.twilio.com/blog/get-started-docker-laravel
https://laravel-for-newbie.kejyun.com/en/advanced/scheduling/docker/
https://github.com/mohammadain/laravel-docker-cron/blob/master/Dockerfile -->

<!-- Improved compatibility of back to top link: See: https://github.com/othneildrew/Best-README-Template/pull/73 -->

<a name="readme-top"></a>

<!-- PROJECT LOGO -->
<br />
<div align="center">
  <a href="https://github.com/rconfig/rconfig">
            <img src="https://portal.rconfig.com/images/rconfig-logos/v8-core-logo-and-icon/rconfig_v8_core_logo_and_icon_with_strapline_gradient.png" alt="rConfig Logo" />
  </a>

  <h3 align="center">rConfig v8 Core</h3>

  <p align="center">
    rConfig v8 Core is a free, open source, community edition of rConfig v8. It is a fully functional version of rConfig v8, with all the core features of rConfig v8 Professional, but with some limitations. Check out our <a href="https://www.rconfig.com/pricing#full-features"><strong>features list</strong></a> to learn more.
    <br />
    <br />
    <a href="https://v8coredocs.rconfig.com"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="#intro">Intro</a>
    ·
    <a href="#setup">Installation</a>
    ·
    <a href="#update">Updating</a>
    ·
    <a href="#contributing">Contributing</a>
    ·
    <a href="#license">License</a>
    ·
    <a href="#support">Support</a>
  </p>

[![Tests](https://github.com/eliashaeussler/typo3-badges/actions/workflows/tests.yaml/badge.svg)](https://github.com/eliashaeussler/typo3-badges/actions/workflows/tests.yaml)
[![License](https://img.shields.io/github/license/eliashaeussler/typo3-badges)](LICENSE) [![Made with Node](https://img.shields.io/badge/dynamic/json?label=node&query=%24.engines%5B%22node%22%5D&url=https%3A%2F%2Fraw.githubusercontent.com%2FMichaelCurrin%2Fbadge-generator%2Fmaster%2Fpackage.json)](https://nodejs.org 'Go to Node.js homepage')
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.3-777BB4?logo=php&logoColor=white)](https://www.php.net/)

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

rConfig v8 is an enterprise-grade Network Configuration Management (NCM) software package designed to help you easily manage configurations across large and small heterogeneous networks. With superior NCM features and capabilities, rConfig v8 streamlines network management operations for high-value networks and business-critical environments.

rConfig v8 runs natively on many Linux distributions, offering flexibility and reliability for your infrastructure. This repository contains the code base for rConfig v8 Core—the free, open-source community edition—along with setup scripts to help you get started quickly.

rConfig v8 Professional, our flagship commercial version, offers additional enterprise features, priority support, and advanced integrations for organizations requiring enhanced capabilities.

If you are looking for rConfig v8 professional, please visit `https://www.rconfig.com/`.

Supported OS

- Rocky Linux 9 (recommended)
- CentOS 9
- RHEL 9
- Ubuntu 22.04+
- Alma Linux 9+
- AWS Linux 2023
- **Now run rConfig v8 Core in a Docker container!** [rconfigv8coredocker repository](https://github.com/rconfig/rconfigv8coredocker)

rConfig v8 core is completely free, you do not need an API key from rConfig to download and use this version. Please follow the instructions below to get started.

## rConfig Editions

| Feature                          | rConfig Core        | rConfig Professional |
|----------------------------------|---------------------|----------------------|
| **Target Audience**              | Individuals & small organizations | Medium-sized businesses to 1,000 devices |
| **License**                      |                     |                      |
| Active Users                     | Unlimited           | All Core Features    |
| Active Devices                   | Unlimited           | Perpetual License    |
| Backups                          | Unlimited           | Subscription for support/Updates |
| **Core Features**                |                     |                      |
| Modern UI                        | ✓                   | ✓                    |
| SSO Integration                  | ✓                   | ✓                    |
| Import from Oxidized             | ✓                   | ✓                    |
| **Support**                      |                     |                      |
| Github for issues                | Best effort         | ✗                    |
| Phone Support                    | ✗                   | ✓                    |
| Email Support                    | ✗                   | ✓                    |
| Priority Support & Updates       | ✗                   | Priority Support 8X5 NBD |
| All major and minor updates      | ✗                   | ✓                    |
| **Advanced Features**            |                     |                      |
| Config Versioning                | ✗                   | ✓                    |
| Snippets/Automations             | ✗                   | ✓                    |
| All roadmap feature updates      | ✗                   | ✓                    |
| API/Integrations                 | ✗                   | ✓                    |
| Advanced customisations          | ✗                   | ✓                    |

_Check out more on our [features page](https://www.rconfig.com/pricing#full-features)._

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- Installation -->

<a name="setup"></a>

## Installation

We have made it super easy to get started with rConfig v8. Follow the steps below to get started. Check out our youtube channel for a video walkthrough of the installation process.

[https://www.youtube.com/channel/rconfigv8Core](https://www.youtube.com/playlist?list=PL8dpV2hQIDLR04p5RuJEVcdhQY1gXKOgU)

> [!NOTE]  
> Do not follow the installation steps to update existing rConfig installations. You must skip to the <a href="#update">Updating</a> section below.

### Prerequisites

Setup a server with one of the supported OS's listed above. We recommend using a fresh install of the OS. If you are using a server that has been in production, we recommend you backup the server before proceeding.

You will need to install the following software on your server:
Git 2.25+
PHP 8.3+
Composer 2.4+
Apache 2.4+
MySQL 5.7+ or MariaDB 10.5+
nodejs 14.17+
Supervisor 4.2+

We have conveniently provided scripts to help you install the required software. Head over to [https://docs.rconfig.com/getstarted/os-setup](https://docs.rconfig.com/getstarted/os-setup) to find the correct script for your OS. If you are using a different OS, you will need to install the required software manually.

> **Note**
> You will need to be logged in as root when running the scripts.

### Database Setup

1. Login to your database server as root
2. Create a new database

```sh
mysql -u root -p
CREATE DATABASE rconfig;
```

3. Create a new user if required (do not use the credentials below in production). This is likely required for Ubutnu 22.04+. If you are using a different OS, you may not need to create a new user, and you can use the root account, though this is not recommended.

```sh

CREATE USER 'user1'@'localhost' IDENTIFIED BY 'password1';
GRANT ALL PRIVILEGES ON rconfig.* TO 'user1'@'localhost';
FLUSH PRIVILEGES;
```

4. Exit the database

```sh
exit
```

### rConfig Setup Steps

1. Login as root
2. Clone the repo

```sh
cd /var/www/html
git clone https://github.com/rconfig/rconfig.git
```

3. Change directory to the repo

```sh
cd rconfig
```

4. Create the .env file

```sh
cp .env.example .env
```

5. Edit the .env file and update the following variables:

```sh
APP_URL="https://SERVER.DOMAIN.LOCAL"
APP_DIR_PATH=/var/www/html/rconfig
DB_HOST=DBHOST
DB_PORT=3306
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

7. Install apache and supervisor

```sh
chmod +x setup_apache.sh
./setup_apache.sh
```

8. Install supervisor

```sh
chmod +x setup_supervisor.sh
./setup_supervisor.sh
```

9. Run the installation script. This will install the required packages, setup the database, and configure the web server. The script will take a few minutes to complete.

> [!WARNING]  
> Do not run the next command on existing rConfig deployments in an attempt to update. You must follow the instructions in the <a href="#update">Updating</a> section below. The command below is for new installations only.

```sh
php artisan install
```

If you area asked 'Add a cron entry for task scheduling?', type `yes` and press enter.

The output from the above should look like this:

```sh


                                         APPLICATION INSTALL.


  Packages discovery ...................................................................... 5ms DONE

  command key:generate .................................................................... 2ms DONE
  command migrate
....................................................................... 387ms DONE
  command passport:install ............................................................ 1,839ms DONE
  command rconfig:clear-all
No config updates to processes


> Illuminate\Foundation\ComposerScripts::postAutoloadDump
Generated optimized autoload files containing 6960 classes
........................................................... 7,036ms DONE
  command rconfig:sync-tasks .............................................................. 5ms DONE
  script  cache .......................................................................... 57ms DONE

  Assets publishing ....................................................................... 7ms DONE

  Add a cron entry for task scheduling? (yes/no) [no]
❯ yes

   INFO  Entry was added [* * * * * cd /var/www/html/rconfig && php artisan schedule:run >> /dev/null 2>&1].

   INFO  Install done!

```

10. Update apache config file for correct server name.

```sh
# CentOS/RHEL
sudo vi /etc/httpd/conf.d/rconfig-vhost.conf
```

```sh
# Ubuntu
sudo vi /etc/apache2/sites-enabled/rconfig-vhost.conf
```

Update the `ServerName` to match your server's domain name.

```sh
ServerName YourServerName.domain.local
ServerAlias YourServerName.domain.local
```

11. Restart apache

```sh
# CENTOS/RHEL
sudo systemctl restart httpd
```

```sh
# Ubuntu
sudo systemctl restart apache2
```

12. Clear the cache & reset permissions

```sh
# CENTOS/RHEL
cd /var/www/html/rconfig
chown -R apache storage bootstrap/cache
php artisan rconfig:clear-all
```

```sh
# Ubuntu
cd /var/www/html/rconfig
chown -R www-data storage bootstrap/cache
php artisan rconfig:clear-all
```

13. Open your browser and navigate to your server's domain name. You should see the rConfig login page. The default system credentials are below. Please change or remove these as soon as a new admin user is created.

Username: admin@domain.com
Password: admin

Check out our docs [v8docs.rconfig.com](https://v8docs.rconfig.com) to learn more.

<!-- Docker Option -->

<a name="docker"></a>

## Running rConfig v8 Core in Docker

We are excited to announce that you can now run **rConfig v8 Core** in a Docker container! This new option simplifies the installation and setup process, providing an isolated environment for running rConfig v8 core without the need for extensive system configurations.

For detailed instructions on setting up rConfig v8 Core in Docker, visit the [rconfig8coredocker repository](https://github.com/rconfig/rconfig8coredocker). 

### Benefits of Docker for rConfig v8
- **Easy Setup**: Quickly deploy rConfig v8 with minimal configuration.
- **Isolation**: Run rConfig v8 in a clean and isolated containerized environment.
- **Portability**: Easily move or replicate your rConfig v8 setup across different systems.
- **Consistency**: Ensure that rConfig v8 behaves the same across different machines and environments.

### Quick Start
To run rConfig v8 in Docker, follow these steps:

1. Clone the `rconfig8coredocker` repository:
    ```bash
    git clone https://github.com/rconfig/rconfig8coredocker.git
    ```

2. Navigate to the cloned directory:
    ```bash
    cd rconfig8coredocker
    ```

3. Build and start the Docker container:
    ```bash
    docker-compose up -d
    ```

4. Access rConfig v8 by navigating to `http://localhost:8080` in your browser.

For more advanced configuration options, check out the [rconfig8coredocker documentation](https://github.com/rconfig/rconfig8coredocker).

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- Updating -->

<a name="update"></a>

## Updating

> [!IMPORTANT]  
> Your advised to backup your database, .env file and storage directory at a minimum before proceeding. You should also backup your entire server if possible.

Instruction on how to update your installation of rConfig v8 Core are below. You should run the commands below as root, and you may need to use sudo if installed on Ubuntu.

> [!WARNING]
> If you edit any of the files in the rConfig directory, you may need to resolve conflicts when you run the `git pull` command. You should be familiar with git and how to resolve conflicts. If you are not, you should not edit any of the files in the rConfig directory. If you get a warning about conflicts, you can try a `git stash` and `git pull` to resolve the conflicts. If you are not familiar with git, you should seek help from a professional.

> [!IMPORTANT]  
> As of Feb 2024, you will need to update PHP to version 8.3 when updating rConfig Core v8. You will also need to update the composer version to 2.4. You can do this by running the following commands:

```sh
# Update PHP CentOS/RHEL/ Rocky
cd /home
yum -y install wget
wget https://www.rconfig.com/downloads/php-updates/centos-php8-update.sh -O /home/centos-php8-update.sh
chmod +x centos-php8-update.sh
./centos-php8-update.sh

# Update PHP Ubuntu
cd /home
sudo apt-get install wget
wget https://www.rconfig.com/downloads/php-updates/ubuntu-php8-update.sh -O /home/ubuntu-php8-update.sh
chmod +x ubuntu-php8-update.sh
./ubuntu-php8-update.sh
```

rConfig V8 professional subscribers should follow the instructions in the rConfig V8 professional documentation. As the installation and update process is different.

```sh
# Centos/Rocky/RHEL
cd /var/www/html/rconfig
git pull
php artisan migrate
php artisan rconfig:sync-tasks
composer install
systemctl restart httpd
php artisan rconfig:clear-all
```

```sh
# Ubuntu
cd /var/www/html/rconfig
git pull
php artisan migrate
php artisan rconfig:sync-tasks
composer install
systemctl restart apache2
php artisan rconfig:clear-all
```

<!-- CONTRIBUTING -->

<a name="contributing"></a>

## Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**. We are pretty strict on code quality and style. Please follow the best practices. You should also have a strong working knowledge of PHP, Laravel, and VueJS.

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

This code base for this repository's code is distributed under License from rConfig. See `LICENSE.txt` for more information. rConfig v8 Professional is excluded from this license and repository.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- https://github.com/othneildrew/Best-README-Template/blob/master/README.md -->

<a name="support"></a>

## Support

Although we provide this code free and open source, rConfig v8 core is based best effort support basis. You may open issues in the issue section here at github. We will try to address issues in a timely manner, but without guarantees. For prompt support and business critical environments, you should take out a subscription for rConfig v8 Professional. rConfig Professional subscribers should open a ticket via our normal support channels.

## Acknowledgments

Inspiration, code snippets, etc.

- [Laravel](https://www.laravel.com)
- [vuejs](https://vuejs.org/)
- [patternfly v4](https://v4-archive.patternfly.org/v4/)

See composer.json and package.json for a full list of dependencies, and their licenses.