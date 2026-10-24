<?php

/**
 * /etc/os-release as each supported OS ships it, trimmed to the lines that
 * matter. Each entry is [contents, expected ID, expected family], where the
 * family is the one setup_supervisor.sh and setup_cron.sh print.
 */
dataset('supported os-release files', [
    'Rocky Linux 8' => [<<<'OS'
        NAME="Rocky Linux"
        VERSION="8.10 (Green Obsidian)"
        ID="rocky"
        ID_LIKE="rhel centos fedora"
        VERSION_ID="8.10"
        PLATFORM_ID="platform:el8"
        PRETTY_NAME="Rocky Linux 8.10 (Green Obsidian)"
        OS, 'rocky', 'rhel'],
    'Rocky Linux 9' => [<<<'OS'
        NAME="Rocky Linux"
        VERSION="9.6 (Blue Onyx)"
        ID="rocky"
        ID_LIKE="rhel centos fedora"
        VERSION_ID="9.6"
        PLATFORM_ID="platform:el9"
        PRETTY_NAME="Rocky Linux 9.6 (Blue Onyx)"
        REDHAT_SUPPORT_PRODUCT="Rocky Linux"
        OS, 'rocky', 'rhel'],
    'AlmaLinux 8' => [<<<'OS'
        NAME="AlmaLinux"
        VERSION="8.10 (Cerulean Leopard)"
        ID="almalinux"
        ID_LIKE="rhel centos fedora"
        VERSION_ID="8.10"
        PLATFORM_ID="platform:el8"
        PRETTY_NAME="AlmaLinux 8.10 (Cerulean Leopard)"
        OS, 'almalinux', 'rhel'],
    'AlmaLinux 9' => [<<<'OS'
        NAME="AlmaLinux"
        VERSION="9.6 (Sage Margay)"
        ID="almalinux"
        ID_LIKE="rhel centos fedora"
        VERSION_ID="9.6"
        PLATFORM_ID="platform:el9"
        PRETTY_NAME="AlmaLinux 9.6 (Sage Margay)"
        OS, 'almalinux', 'rhel'],
    'RHEL 8' => [<<<'OS'
        NAME="Red Hat Enterprise Linux"
        VERSION="8.10 (Ootpa)"
        ID="rhel"
        ID_LIKE="fedora"
        VERSION_ID="8.10"
        PLATFORM_ID="platform:el8"
        PRETTY_NAME="Red Hat Enterprise Linux 8.10 (Ootpa)"
        OS, 'rhel', 'rhel'],
    'RHEL 9' => [<<<'OS'
        NAME="Red Hat Enterprise Linux"
        VERSION="9.6 (Plow)"
        ID="rhel"
        ID_LIKE="fedora"
        VERSION_ID="9.6"
        PLATFORM_ID="platform:el9"
        PRETTY_NAME="Red Hat Enterprise Linux 9.6 (Plow)"
        OS, 'rhel', 'rhel'],
    'CentOS Linux 8' => [<<<'OS'
        NAME="CentOS Linux"
        VERSION="8"
        ID="centos"
        ID_LIKE="rhel fedora"
        VERSION_ID="8"
        PLATFORM_ID="platform:el8"
        PRETTY_NAME="CentOS Linux 8"
        OS, 'centos', 'rhel'],
    'CentOS Stream 9' => [<<<'OS'
        NAME="CentOS Stream"
        VERSION="9"
        ID="centos"
        ID_LIKE="rhel fedora"
        VERSION_ID="9"
        PLATFORM_ID="platform:el9"
        PRETTY_NAME="CentOS Stream 9"
        OS, 'centos', 'rhel'],
    'Ubuntu 22.04' => [<<<'OS'
        PRETTY_NAME="Ubuntu 22.04.5 LTS"
        NAME="Ubuntu"
        VERSION_ID="22.04"
        VERSION="22.04.5 LTS (Jammy Jellyfish)"
        VERSION_CODENAME=jammy
        ID=ubuntu
        ID_LIKE=debian
        UBUNTU_CODENAME=jammy
        OS, 'ubuntu', 'debian'],
    'Ubuntu 24.04' => [<<<'OS'
        PRETTY_NAME="Ubuntu 24.04.3 LTS"
        NAME="Ubuntu"
        VERSION_ID="24.04"
        VERSION="24.04.3 LTS (Noble Numbat)"
        VERSION_CODENAME=noble
        ID=ubuntu
        ID_LIKE=debian
        UBUNTU_CODENAME=noble
        OS, 'ubuntu', 'debian'],
    'Amazon Linux 2023' => [<<<'OS'
        NAME="Amazon Linux"
        VERSION="2023"
        ID="amzn"
        ID_LIKE="fedora"
        VERSION_ID="2023"
        PLATFORM_ID="platform:al2023"
        PRETTY_NAME="Amazon Linux 2023.8.20250818"
        SUPPORT_END="2029-06-30"
        OS, 'amzn', 'rhel'],
]);

/**
 * os-release files the setup scripts must refuse. Fedora is listed because
 * RHEL and Amazon Linux both say ID_LIKE="fedora", so matching on fedora
 * would accept a distribution rConfig does not support.
 */
dataset('unsupported os-release files', [
    'Fedora' => "NAME=\"Fedora Linux\"\nID=fedora\nVERSION_ID=42\n",
    'openSUSE Leap' => "NAME=\"openSUSE Leap\"\nID=\"opensuse-leap\"\nID_LIKE=\"suse opensuse\"\n",
    'Arch Linux' => "NAME=\"Arch Linux\"\nID=arch\n",
    'no ID line' => "NAME=\"Mystery Linux\"\n",
]);
