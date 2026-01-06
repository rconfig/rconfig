<!-- Improved compatibility of back to top link -->
<a name="readme-top"></a>

<!-- PROJECT LOGO -->
<br />
<div align="center">
  <a href="https://github.com/rconfig/rconfig">
    <img src="https://portal.rconfig.com/images/rconfig-logos/v8-core-logo-and-icon/rconfig_v8_core_logo_and_icon_with_strapline_gradient.png" alt="rConfig Logo" width="600"/>
  </a>

  <h1 align="center">rConfig v8 Core</h1>

  <p align="center">
    <strong>Enterprise-Grade Network Configuration Management</strong>
    <br />
    Free, Open Source, Community Edition
    <br />
    <br />
    <a href="https://v8coredocs.rconfig.com"><strong>📚 Explore the Docs »</strong></a>
    <br />
    <br />
    <a href="#quick-start">Quick Start</a>
    ·
    <a href="#features">Features</a>
    ·
    <a href="#installation">Installation</a>
    ·
    <a href="https://github.com/rconfig/rconfig/issues">Report Bug</a>
    ·
    <a href="https://github.com/rconfig/rconfig/issues">Request Feature</a>
  </p>

  <!-- Badges -->
  <p align="center">
    <a href="https://github.com/rconfig/rconfig/actions">
      <img src="https://github.com/eliashaeussler/typo3-badges/actions/workflows/tests.yaml/badge.svg" alt="Tests"/>
    </a>
    <a href="LICENSE">
      <img src="https://img.shields.io/github/license/rconfig/rconfig" alt="License"/>
    </a>
    <a href="https://github.com/rconfig/rconfig/stargazers">
      <img src="https://img.shields.io/github/stars/rconfig/rconfig?style=social" alt="GitHub stars"/>
    </a>
  </p>

  <!-- Technology Badges -->
  <p align="center">
    <img src="https://img.shields.io/badge/PHP-8.4+-777BB4?logo=php&logoColor=white&style=for-the-badge" alt="PHP 8.4+"/>
    <img src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white&style=for-the-badge" alt="Laravel"/>
    <img src="https://img.shields.io/badge/Vue.js-3-4FC08D?logo=vue.js&logoColor=white&style=for-the-badge" alt="Vue 3"/>
    <img src="https://img.shields.io/badge/Vite-Latest-646CFF?logo=vite&logoColor=white&style=for-the-badge" alt="Vite"/>
    <img src="https://img.shields.io/badge/MySQL-8.0+-4479A1?logo=mysql&logoColor=white&style=for-the-badge" alt="MySQL"/>
  </p>
</div>

---

## 🎯 About rConfig v8 Core

rConfig v8 Core is a **powerful, free, and open-source** Network Configuration Management (NCM) solution designed to help you easily manage configurations across networks of any size—from small deployments to large, heterogeneous enterprise environments.

### Why Choose rConfig?

- 🚀 **Fast & Efficient** - Optimized for high-performance configuration backups
- 🔒 **Secure** - Built with security best practices from the ground up
- 🌐 **Multi-Vendor Support** - Works with Cisco, Juniper, HP, and more
- 📦 **Easy Deployment** - Docker support for quick setup
- 💰 **Cost-Free** - No licensing fees, truly open source
- 🛠️ **Actively Maintained** - Regular updates and community support

<p align="right">(<a href="#readme-top">⬆ back to top</a>)</p>

---
## 📸 Screenshots

<details>

<summary><strong>Click to view screenshots</strong></summary>

### Dashboard
![Dashboard](public/images/brand/dashboard.png)

</details>

## ✨ Features

<table>
<tr>
<td width="50%">

### Core Features
- ✅ **Configuration Backup** - Automated device backups
- ✅ **Multi-Vendor Support** - Cisco, Juniper, HP, Dell, and more
- ✅ **Unlimited Devices** - No artificial limits
- ✅ **Scheduled Tasks** - Automated backup scheduling
- ✅ **Version Control** - Track configuration changes over time
- ✅ **Search & Compare** - Powerful config search and diff tools

</td>
<td width="50%">

### Technical Stack
- 🔧 **Laravel 12** - Modern PHP framework
- 🎨 **Vue.js 3** - Reactive UI components
- ⚡ **Vite** - Lightning-fast builds
- 🎨 **shadcn/ui** - Beautiful UI components
- 🐳 **Docker Ready** - Container deployment
- 📊 **MySQL/MariaDB** - Reliable database

</td>
</tr>
</table>

### 🆚 rConfig Editions Comparison

| Feature              | 🆓 rConfig Core | 💎 rConfig Professional |
|----------------------|:---------------:|:-----------------------:|
| Configuration Backup | ✅              | ✅                      |
| Multi-Vendor Support | ✅              | ✅                      |
| Unlimited Devices    | ✅              | ✅                      |
| API Access           | ❌              | ✅                      |
| Enterprise Features  | ❌              | ✅                      |
| Priority Support     | ❌              | ✅                      |
| SLA Guarantees       | ❌              | ✅                      |

<details>
<summary><strong>📋 View Full Feature Comparison</strong></summary>
<br>
Check out the complete feature list at <a href="https://www.rconfig.com/pricing#full-features">rconfig.com/pricing</a>
</details>

<p align="right">(<a href="#readme-top">⬆ back to top</a>)</p>

---

## 🚀 Quick Start

Get rConfig v8 Core up and running in minutes!

### Option 1: 🐳 Docker (Recommended for Quick Testing)
```bash
# Clone the Docker repository
git clone https://github.com/rconfig/rconfig8coredocker.git
cd rconfig8coredocker

# Start the containers
docker-compose up -d

# Access at http://localhost:8080
```

**Default credentials:**
- 📧 Email: `admin@domain.com`
- 🔑 Password: `admin`

> ⚠️ **Important:** Change these credentials immediately after first login!

### Option 2: 💻 Native Installation

See the [Full Installation Guide](#installation) below.

<p align="right">(<a href="#readme-top">⬆ back to top</a>)</p>

---

## 📦 Installation

### Prerequisites

Before you begin, ensure you have:

- ✅ One of the supported operating systems:
  - Rocky Linux 8/9+ (recommended)
  - CentOS 8/9+
  - RHEL 8/9+
  - Ubuntu 22.04+
  - Alma Linux 8/9+
  - AWS Linux 2023

- ✅ Required software:
  - Git 2.25+
  - PHP 8.4+
  - Composer 2.4+
  - Apache 2.4+
  - MySQL 5.7+ or MariaDB 10.5+
  - Node.js 14.17+
  - Supervisor 4.2+

> 💡 **Tip:** We provide automated setup scripts! Visit [docs.rconfig.com/getstarted/os-setup](https://docs.rconfig.com/getstarted/os-setup)

---

### 🗄️ Database Setup
```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE rconfig;

# Create user (recommended for Ubuntu 22.04+)
CREATE USER 'rconfig_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON rconfig.* TO 'rconfig_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

### 📥 Installation Steps
```bash
# 1. Navigate to web directory
cd /var/www/html

# 2. Clone the repository
git clone https://github.com/rconfig/rconfig.git
cd rconfig

# 3. Create environment file
cp .env.example .env

# 4. Edit .env with your settings
nano .env
```

**Update these variables in `.env`:**
```env
APP_URL="https://your-server.domain.com"
APP_DIR_PATH=/var/www/html/rconfig
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=rconfig
DB_USERNAME=rconfig_user
DB_PASSWORD=your_secure_password
```
```bash
# 5. Install PHP dependencies
export COMPOSER_ALLOW_SUPERUSER=1
composer self-update --2
yes | composer install --no-dev

# 6. Setup Apache
chmod +x setup_apache.sh
./setup_apache.sh

# 7. Setup Supervisor
chmod +x setup_supervisor.sh
./setup_supervisor.sh

# 8. Run the beautiful installation wizard 🎨
php artisan v8core:install
```

> 🎉 When prompted about cron scheduling, type `yes` and press Enter.

---

### 🔧 Final Configuration
```bash
# Update Apache virtual host
# For CentOS/RHEL:
sudo nano /etc/httpd/conf.d/rconfig-vhost.conf

# For Ubuntu:
sudo nano /etc/apache2/sites-enabled/rconfig-vhost.conf
```

Update `ServerName`:
```apache
ServerName your-server.domain.com
ServerAlias your-server.domain.com
```
```bash
# Restart Apache
# CentOS/RHEL:
sudo systemctl restart httpd

# Ubuntu:
sudo systemctl restart apache2

# Set permissions and clear cache
# CentOS/RHEL:
cd /var/www/html/rconfig
chown -R apache storage bootstrap/cache
php artisan rconfig:clear-all

# Ubuntu:
cd /var/www/html/rconfig
chown -R www-data storage bootstrap/cache
php artisan rconfig:clear-all
```

---

### 🎊 Access Your Installation

Open your browser and navigate to: `https://your-server.domain.com`

**Default System Credentials:**
- 📧 Email: `admin@domain.com`
- 🔑 Password: `admin`

> ⚠️ **Security Notice:** Change or remove these credentials immediately after creating a new admin user!

<p align="right">(<a href="#readme-top">⬆ back to top</a>)</p>

---

## 🐳 Docker Installation

Running rConfig v8 Core in Docker provides:

- ✨ **Easy Setup** - Get started in minutes
- 🔒 **Isolation** - Clean containerized environment  
- 🚚 **Portability** - Move between systems easily
- 📦 **Consistency** - Same behavior everywhere

### Quick Docker Setup
```bash
# Clone the Docker repository
git clone https://github.com/rconfig/rconfig8coredocker.git
cd rconfig8coredocker

# Build and start containers
docker-compose up -d

# Access at http://localhost:8080
```

📖 **Full Docker documentation:** [rconfig8coredocker repository](https://github.com/rconfig/rconfig8coredocker)

<p align="right">(<a href="#readme-top">⬆ back to top</a>)</p>

---

## 🔄 Updating

> ⚠️ **Before updating:**
> - Backup your database
> - Backup your `.env` file
> - Backup your `storage` directory
> - Backup your entire server if possible

### PHP 8.4 Update (Required as of Feb 2024)
```bash
# For CentOS/RHEL/Rocky:
cd /home
yum -y install wget
wget https://www.rconfig.com/downloads/php-updates/centos-php8-update.sh -O /home/centos-php8-update.sh
chmod +x centos-php8-update.sh
./centos-php8-update.sh

# For Ubuntu:
cd /home
sudo apt-get install wget
wget https://www.rconfig.com/downloads/php-updates/ubuntu-php8-update.sh -O /home/ubuntu-php8-update.sh
chmod +x ubuntu-php8-update.sh
./ubuntu-php8-update.sh
```

### Update Commands
```bash
# CentOS/Rocky/RHEL:
cd /var/www/html/rconfig
git pull
php artisan migrate
php artisan rconfig:sync-tasks
composer install
systemctl restart httpd
php artisan rconfig:clear-all

# Ubuntu:
cd /var/www/html/rconfig
git pull
php artisan migrate
php artisan rconfig:sync-tasks
composer install
systemctl restart apache2
php artisan rconfig:clear-all
```

> 💡 **Git Conflicts?** Try: `git stash && git pull`

<p align="right">(<a href="#readme-top">⬆ back to top</a>)</p>

---

## 🤝 Contributing

Contributions make the open-source community amazing! Any contributions you make are **greatly appreciated**.

### Contribution Guidelines

We maintain high standards for code quality and style. Contributors should have:
- Strong working knowledge of PHP, Laravel, and Vue.js
- Understanding of best practices and coding standards
- Ability to write clean, maintainable code

### How to Contribute

1. 🍴 Fork the Project
2. 🌿 Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. ✍️ Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. 📤 Push to the Branch (`git push origin feature/AmazingFeature`)
5. 🔀 Open a Pull Request to the `develop` branch

### Running Tests
```bash
# 1. Create test database
# 2. Copy environment file
cp .env.example .env.testing

# 3. Generate test key
php artisan key:generate --env=testing

# 4. Update .env.testing
# Set APP_ENV=testing
# Update database credentials

# 5. Run tests
php artisan test
```

### Frontend Development
```bash
# Install dev dependencies
npm install --include=dev

# Start dev server
npm run dev
```

> 💡 **Network issues with npm?** Try: `export NODE_OPTIONS="--dns-result-order=ipv4first"`

<p align="right">(<a href="#readme-top">⬆ back to top</a>)</p>

---

## 📺 Video Tutorials

Check out our YouTube channel for installation walkthroughs and tutorials:

[![YouTube](https://img.shields.io/badge/YouTube-rConfig-red?style=for-the-badge&logo=youtube)](https://www.youtube.com/playlist?list=PL8dpV2hQIDLR04p5RuJEVcdhQY1gXKOgU)

<p align="right">(<a href="#readme-top">⬆ back to top</a>)</p>

---

## 📄 License

This codebase is distributed under License from rConfig. See [`LICENSE.txt`](LICENSE.txt) for more information.

> ℹ️ rConfig v8 Professional is excluded from this license and repository.

<p align="right">(<a href="#readme-top">⬆ back to top</a>)</p>

---

## 💬 Support

### Community Support (rConfig Core)

- 🐛 [Report Issues](https://github.com/rconfig/rconfig/issues)
- 💡 [Request Features](https://github.com/rconfig/rconfig/issues/new)
- 📖 [Documentation](https://v8coredocs.rconfig.com)
- ⭐ [Star us on GitHub](https://github.com/rconfig/rconfig)

> ℹ️ rConfig v8 Core is provided on a **best-effort basis**. Response times may vary.

### Priority Support (rConfig Professional)

For business-critical environments and guaranteed response times:
- 🎫 Dedicated support portal
- 📞 Priority response SLA
- 🔧 Expert assistance
- 📊 Advanced features

👉 [Learn more about rConfig Professional](https://www.rconfig.com/)

<p align="right">(<a href="#readme-top">⬆ back to top</a>)</p>

---

## 🙏 Acknowledgments

Built with amazing open-source technologies:

- [Laravel](https://laravel.com) - The PHP Framework for Web Artisans (V12)
- [Vue.js](https://vuejs.org/) - The Progressive JavaScript Framework
- [shadcn/ui](https://ui.shadcn.com/) - Beautifully designed components
- [Vite](https://vitejs.dev/) - Next Generation Frontend Tooling

See [`composer.json`](composer.json) and [`package.json`](package.json) for the complete list of dependencies.

<p align="right">(<a href="#readme-top">⬆ back to top</a>)</p>

---

<div align="center">

### ⭐ Star us on GitHub — it motivates us a lot!

[![GitHub stars](https://img.shields.io/github/stars/rconfig/rconfig?style=social)](https://github.com/rconfig/rconfig/stargazers)

**Made with ❤️ by the rConfig Team**

[Website](https://www.rconfig.com) · [Documentation](https://v8coredocs.rconfig.com) · [Twitter](https://twitter.com/rconfig) · [GitHub](https://github.com/rconfig)

</div>