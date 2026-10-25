#!/usr/bin/env bash
#
# Installs the rConfig V8 Core scheduler cron file and removes the old
# schedule:run line from root's crontab and the web user's crontab.
#
# The scheduler runs from /etc/cron.d/rconfig-core as the web user. It never
# runs from root's crontab: that would run artisan as root on code the web
# user owns, which lets the web user run any command as root.
#
# The cron.d file is root:root 0644. cron silently skips cron.d files that are
# group- or world-writable, and Debian and Ubuntu cron silently skip names
# with a dot in them, so both are checked before the file goes into place.
#
# Run as root, from the rConfig app folder:   sudo ./setup_cron.sh
#
# Safe to run more than once. Every check runs before anything is written. If
# a crontab cannot be rewritten, the cron.d file and any crontab already
# rewritten are put back, so the scheduler never ends up running twice.
#
# Only schedule:run lines that run this app folder are removed from a crontab,
# matched word by word so /var/www/html/rconfig8 does not count as
# /var/www/html/rconfig. Every other line, including comments, is kept, and a
# copy of the old crontab is saved first.
#
# Optional environment, used by the test suite:
#   RCONFIG_CRON_DIR             write the cron file into this folder instead
#                                of /etc/cron.d. Test mode: restorecon is not
#                                run and the root check is skipped.
#   RCONFIG_CRONTAB              crontab binary. Required in test mode, so a
#                                test can never reach the real crontabs.
#   RCONFIG_CRONTAB_BACKUP_DIR   where copies of rewritten crontabs go.
#                                Defaults to /root, required in test mode.
#   RCONFIG_SYSTEM_CRONTAB       system crontab to check for a duplicate
#                                scheduler line. Defaults to /etc/crontab, and
#                                is not checked in test mode unless set.
#   RCONFIG_PHP                  PHP binary used to run artisan. Defaults to php.
#   RCONFIG_OS_RELEASE           os-release file to detect the OS from.
#                                Defaults to /etc/os-release.

set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)"
APP_DIR_LOGICAL="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -L)"
PHP_BIN="${RCONFIG_PHP:-php}"

# CORE uses its own name because rConfig PRO may install a cron file on the
# same box.
CRON_NAME=rconfig-core

fail() {
    echo "setup_cron: ERROR: $*" >&2
    exit 1
}

info() {
    echo "setup_cron: $*"
}

# --- Work out the OS ---------------------------------------------------------

# Detected from ID and ID_LIKE in /etc/os-release, the file rconfig_web_user()
# reads. It is on every supported OS, and /etc/debian_version says nothing
# about Ubuntu. The file is parsed, never sourced, so nothing in it runs as
# root.
OS_RELEASE="${RCONFIG_OS_RELEASE:-/etc/os-release}"

os_release_value() {
    sed -n "s/^$1=//p" "${OS_RELEASE}" | tail -n 1 \
        | sed -e 's/^"\(.*\)"$/\1/' -e "s/^'\\(.*\\)'\$/\\1/" | tr '[:upper:]' '[:lower:]'
}

[ -r "${OS_RELEASE}" ] || fail "cannot read ${OS_RELEASE}, so the OS cannot be detected."
OS_ID="$(os_release_value ID)"
OS_ID_LIKE="$(os_release_value ID_LIKE)"

# ID comes before ID_LIKE, so a distribution is matched on its own name first.
OS_FAMILY=""
read -ra OS_WORDS <<<"${OS_ID} ${OS_ID_LIKE}"
for word in "${OS_WORDS[@]}"; do
    case "${word}" in
        rhel | centos | rocky | almalinux | ol) OS_FAMILY=rhel; break ;;
        ubuntu | debian) OS_FAMILY=debian; break ;;
    esac
done

case "${OS_FAMILY}" in
    rhel)
        CRON_SERVICE=crond
        CRON_INSTALL="Install it with: dnf install cronie, then: systemctl enable --now crond"
        ;;
    debian)
        CRON_SERVICE=cron
        CRON_INSTALL="Install it with: apt install cron, then: systemctl enable --now cron"
        ;;
    *)
        if [ "${OS_ID}" = "amzn" ]; then
            fail "Amazon Linux is no longer supported from 8.2.19. Stay on your current version and contact support. Supported: Rocky, AlmaLinux, RHEL and CentOS 8 or 9, and Ubuntu 22.04 or later."
        fi
        fail "unsupported OS (ID=${OS_ID:-none}, ID_LIKE=${OS_ID_LIKE:-none} in ${OS_RELEASE}). Supported: Rocky, AlmaLinux, RHEL and CentOS 8 or 9, and Ubuntu 22.04 or later."
        ;;
esac

info "OS: ${OS_ID} (${OS_FAMILY} family), cron service ${CRON_SERVICE}"

# --- Work out the folders and binaries ---------------------------------------

if [ -n "${RCONFIG_CRON_DIR:-}" ]; then
    TEST_MODE=1
    CRON_DIR="${RCONFIG_CRON_DIR}"
    [ -n "${RCONFIG_CRONTAB:-}" ] || fail "RCONFIG_CRON_DIR is set without RCONFIG_CRONTAB. Test mode never uses the real crontab."
    [ -n "${RCONFIG_CRONTAB_BACKUP_DIR:-}" ] || fail "RCONFIG_CRON_DIR is set without RCONFIG_CRONTAB_BACKUP_DIR."
    CRONTAB_BIN="${RCONFIG_CRONTAB}"
    BACKUP_DIR="${RCONFIG_CRONTAB_BACKUP_DIR}"
    SYSTEM_CRONTAB="${RCONFIG_SYSTEM_CRONTAB:-}"
else
    TEST_MODE=0
    CRON_DIR=/etc/cron.d
    CRONTAB_BIN="${RCONFIG_CRONTAB:-crontab}"
    BACKUP_DIR="${RCONFIG_CRONTAB_BACKUP_DIR:-/root}"
    SYSTEM_CRONTAB="${RCONFIG_SYSTEM_CRONTAB:-/etc/crontab}"

    if [ "$(id -u)" -ne 0 ]; then
        fail "this script writes to ${CRON_DIR} and to root's crontab and must be run as root, for example: sudo ./setup_cron.sh"
    fi
fi

# Debian and Ubuntu cron ignore cron.d names containing anything else,
# including a dot, without any warning.
if [[ ! "${CRON_NAME}" =~ ^[A-Za-z0-9_-]+$ ]]; then
    fail "cron file name '${CRON_NAME}' would be ignored by cron. Use letters, digits, hyphens and underscores only."
fi

if [ ! -d "${CRON_DIR}" ]; then
    fail "cron folder ${CRON_DIR} not found, so cron is not installed. ${CRON_INSTALL}"
fi

if [ ! -d "${BACKUP_DIR}" ]; then
    fail "crontab backup folder ${BACKUP_DIR} not found."
fi

if ! command -v "${CRONTAB_BIN}" >/dev/null 2>&1; then
    fail "crontab command '${CRONTAB_BIN}' not found, so cron is not installed. ${CRON_INSTALL}"
fi

for tool in cmp getent awk stat; do
    command -v "${tool}" >/dev/null 2>&1 \
        || fail "required command ${tool} not found. Install it (cmp is in the diffutils package) and run this script again."
done

# The app folder is written into the cron line, which cron hands to /bin/sh.
if [[ ! "${APP_DIR}" =~ ^/[A-Za-z0-9._/-]+$ ]]; then
    fail "app folder '${APP_DIR}' contains characters that cannot be written into a cron file safely."
fi

# --- Detect and validate the web user ----------------------------------------

# rconfig_web_user() is the single source of truth, so the shell side asks
# artisan rather than keeping its own copy of the detection logic. The last
# line is taken in case PHP prints a notice first.
if ! WEB_USER_OUTPUT="$("${PHP_BIN}" "${APP_DIR}/artisan" rconfig:web-user)"; then
    fail "could not detect the web user: '${PHP_BIN} ${APP_DIR}/artisan rconfig:web-user' failed. Run composer install first."
fi
WEB_USER="$(printf '%s\n' "${WEB_USER_OUTPUT}" | tail -n 1 | tr -d '[:space:]')"

if [[ ! "${WEB_USER}" =~ ^[a-z_][a-z0-9_-]*$ ]]; then
    fail "artisan rconfig:web-user returned '${WEB_USER}', which is not a valid user name."
fi

if ! PASSWD_ENTRY="$(getent passwd "${WEB_USER}")"; then
    fail "web user '${WEB_USER}' does not exist on this host. Install and start the web server (httpd or apache2) first, then run this script again."
fi

WEB_UID="$(printf '%s' "${PASSWD_ENTRY}" | cut -d: -f3)"
if [ "${WEB_UID}" = "0" ]; then
    fail "web user '${WEB_USER}' has uid 0. The scheduler must never run as root."
fi

info "web user: ${WEB_USER}"
info "app folder: ${APP_DIR}"
info "cron folder: ${CRON_DIR}"

# --- Working files and rollback ---------------------------------------------

WORK_DIR="$(mktemp -d)"
TMP_CRON=""
CRON_INSTALLED=0
CRON_PREVIOUS=""
declare -a REWRITTEN_USERS=()

cleanup() {
    if [ -n "${TMP_CRON}" ] && [ -e "${TMP_CRON}" ]; then
        rm -f "${TMP_CRON}"
    fi
    rm -rf "${WORK_DIR}"
}
trap cleanup EXIT

# Puts back everything this run changed, then stops. Used when a crontab
# cannot be rewritten, so the box is never left with the cron.d file and an
# old crontab line both running the scheduler.
rollback_and_fail() {
    local message="$1" user

    for user in "${REWRITTEN_USERS[@]}"; do
        if ! "${CRONTAB_BIN}" -u "${user}" "${WORK_DIR}/${user}.orig"; then
            echo "setup_cron: ERROR: could not restore the crontab for ${user}. Its old copy is in ${BACKUP_DIR}." >&2
        fi
    done

    if [ "${CRON_INSTALLED}" -eq 1 ]; then
        if [ -n "${CRON_PREVIOUS}" ]; then
            cp -p "${CRON_PREVIOUS}" "${CRON_TARGET}"
        else
            rm -f "${CRON_TARGET}"
        fi
    fi

    fail "${message} The changes made by this run were rolled back."
}

# --- Read and clean the crontabs ---------------------------------------------

# Whether a line runs this app folder's scheduler. A word counts as this app
# folder if, with quotes and shell punctuation trimmed, it is the folder
# itself or starts with the folder followed by a slash. Both the physical and
# the logical path are checked, in case the folder was reached through a
# symlink. Comments never match.
#
# The single quote comes in as sq, since it cannot appear in this string and
# octal escapes in regexes are not portable between gawk and mawk.
SCHEDULER_LINE_AWK='
    function trim(word) {
        while (length(word) > 0 && index("\"(" sq, substr(word, 1, 1)) > 0) { word = substr(word, 2) }
        while (length(word) > 0 && index("\";&|)" sq, substr(word, length(word), 1)) > 0) { word = substr(word, 1, length(word) - 1) }
        return word
    }
    function is_app_word(word) {
        word = trim(word)
        if (word == dir || word == dir "/" || index(word, dir "/") == 1) { return 1 }
        if (word == logical || word == logical "/" || index(word, logical "/") == 1) { return 1 }
        return 0
    }
    function runs_scheduler(line,    n, i, words, has_schedule, has_dir) {
        if (line ~ /^[[:space:]]*#/) { return 0 }
        n = split(line, words, /[[:space:]]+/)
        has_schedule = 0
        has_dir = 0
        for (i = 1; i <= n; i++) {
            if (trim(words[i]) == "schedule:run") { has_schedule = 1 }
            if (is_app_word(words[i])) { has_dir = 1 }
        }
        return has_schedule && has_dir
    }
'

# Reads a user's crontab into WORK_DIR/<user>.orig. A user with no crontab
# gets an empty file. Any other failure stops the script, because a crontab
# that cannot be read cannot be cleaned safely.
read_crontab() {
    local user="$1" err

    if "${CRONTAB_BIN}" -u "${user}" -l >"${WORK_DIR}/${user}.orig" 2>"${WORK_DIR}/${user}.err"; then
        return 0
    fi

    err="$(cat "${WORK_DIR}/${user}.err")"
    if printf '%s' "${err}" | grep -qi 'no crontab for'; then
        : >"${WORK_DIR}/${user}.orig"
        return 0
    fi

    fail "could not read the crontab for ${user}, nothing was written: ${err}"
}

# Writes the crontab without this app's scheduler lines to <user>.new and the
# removed lines to <user>.removed.
clean_crontab() {
    local user="$1"

    awk -v dir="${APP_DIR}" -v logical="${APP_DIR_LOGICAL}" -v sq="'" \
        -v removed="${WORK_DIR}/${user}.removed" \
        "${SCHEDULER_LINE_AWK}"'
        {
            if (runs_scheduler($0)) { print > removed } else { print }
        }
    ' "${WORK_DIR}/${user}.orig" >"${WORK_DIR}/${user}.new"
    touch "${WORK_DIR}/${user}.removed"
}

CRONTAB_USERS=(root "${WEB_USER}")
declare -a CHANGED_USERS=()

for user in "${CRONTAB_USERS[@]}"; do
    read_crontab "${user}"
    clean_crontab "${user}"
    if [ -s "${WORK_DIR}/${user}.removed" ]; then
        CHANGED_USERS+=("${user}")
    fi
done

# --- Build the cron file and check it ----------------------------------------

CRON_TARGET="${CRON_DIR}/${CRON_NAME}"
CRON_LINE="* * * * * ${WEB_USER} cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1"

# Created in the target folder so the final mv is an atomic rename on the same
# filesystem, and the file picks up the folder's SELinux context. The leading
# dot keeps cron from loading it while it is being written.
TMP_CRON="$(mktemp "${CRON_DIR}/.${CRON_NAME}.XXXXXX")"
cat >"${TMP_CRON}" <<EOF
# rConfig V8 Core scheduler. Written by setup_cron.sh, do not edit: it is
# rewritten on every install and upgrade. Runs as the web user, never root.
SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
${CRON_LINE}
EOF
chmod 0644 "${TMP_CRON}"
if [ "$(id -u)" -eq 0 ]; then
    chown root:root "${TMP_CRON}"
fi

# Checks the cron file at the given path is one cron will load and run as the
# web user.
check_cron_file() {
    local path="$1" mode owner user_field

    mode="$(stat -c '%a' "${path}")"
    [ "${mode}" = "644" ] || fail "${path} has mode ${mode}, expected 644. cron skips group- or world-writable files."

    if [ "$(id -u)" -eq 0 ]; then
        owner="$(stat -c '%U:%G' "${path}")"
        [ "${owner}" = "root:root" ] || fail "${path} is owned by ${owner}, expected root:root."
    fi

    [ -z "$(tail -c 1 "${path}")" ] || fail "${path} does not end with a newline. cron ignores the last line without one."

    user_field="$(awk '!/^[[:space:]]*(#|$)/ && !/^[A-Za-z_]+=/ { print $6 }' "${path}")"
    [ "${user_field}" = "${WEB_USER}" ] || fail "${path} runs as '${user_field}', expected ${WEB_USER}."
}

check_cron_file "${TMP_CRON}"

# --- Save copies of the crontabs that will change ----------------------------

STAMP="$(date +%Y%m%d%H%M%S)"
for user in "${CHANGED_USERS[@]}"; do
    backup="${BACKUP_DIR}/crontab-${user}.rconfig-bak-${STAMP}"
    (umask 077 && cp "${WORK_DIR}/${user}.orig" "${backup}")
    chmod 0600 "${backup}"
    info "saved a copy of the crontab for ${user} to ${backup}"
done

# --- Write the cron file and the cleaned crontabs ----------------------------

# An existing file with the right content is only left alone if its mode and
# owner are right too, so a file cron would skip always gets replaced.
if [ -f "${CRON_TARGET}" ] && [ ! -L "${CRON_TARGET}" ] && cmp -s "${CRON_TARGET}" "${TMP_CRON}" \
    && (check_cron_file "${CRON_TARGET}") 2>/dev/null; then
    rm -f "${TMP_CRON}"
    TMP_CRON=""
    info "${CRON_TARGET} is already up to date"
else
    if [ -e "${CRON_TARGET}" ] || [ -L "${CRON_TARGET}" ]; then
        # Kept in the working folder, never in the cron folder: RHEL's cronie
        # loads every file there, whatever its name.
        CRON_PREVIOUS="${WORK_DIR}/cron.previous"
        cp -pL "${CRON_TARGET}" "${CRON_PREVIOUS}" 2>/dev/null || CRON_PREVIOUS=""
        info "replacing ${CRON_TARGET}, which differed or had the wrong mode or owner. Its old content was:"
        sed 's/^/    /' "${CRON_TARGET}" 2>/dev/null || true
    fi

    # rename(2) replaces a symlink itself, never the file it points to.
    mv -fT "${TMP_CRON}" "${CRON_TARGET}"
    TMP_CRON=""
    CRON_INSTALLED=1
    info "wrote ${CRON_TARGET}"

    if [ "${TEST_MODE}" -eq 0 ] && command -v restorecon >/dev/null 2>&1; then
        restorecon -F "${CRON_TARGET}" || info "restorecon failed on ${CRON_TARGET}, check its SELinux context"
    fi
fi

# Run in a subshell so a failed check rolls back instead of leaving a file
# cron would skip, or run as the wrong user.
if ! (check_cron_file "${CRON_TARGET}"); then
    rollback_and_fail "${CRON_TARGET} failed its check after it was moved into place."
fi

for user in "${CHANGED_USERS[@]}"; do
    if ! "${CRONTAB_BIN}" -u "${user}" "${WORK_DIR}/${user}.new"; then
        rollback_and_fail "could not rewrite the crontab for ${user}."
    fi
    REWRITTEN_USERS+=("${user}")
    info "removed this install's scheduler line from the crontab for ${user}:"
    sed 's/^/    /' "${WORK_DIR}/${user}.removed"
done

# --- Warn about other copies of the scheduler --------------------------------

# Not provably safe to remove, so only warn. Each one would run the scheduler
# a second time.
warn_if_runs_scheduler() {
    local path="$1"

    if awk -v dir="${APP_DIR}" -v logical="${APP_DIR_LOGICAL}" -v sq="'" \
        "${SCHEDULER_LINE_AWK}"'
        runs_scheduler($0) { found = 1 }
        END { exit !found }
    ' "${path}"; then
        info "WARNING: ${path} also runs the scheduler for ${APP_DIR}. Remove that line, or the scheduler will run twice."
    fi
}

for other in "${CRON_DIR}"/*; do
    if [ "${other}" != "${CRON_TARGET}" ] && [ -f "${other}" ]; then
        warn_if_runs_scheduler "${other}"
    fi
done

if [ -n "${SYSTEM_CRONTAB}" ] && [ -f "${SYSTEM_CRONTAB}" ]; then
    warn_if_runs_scheduler "${SYSTEM_CRONTAB}"
fi

# cronie is not enabled when it is installed, so the file above would never run.
if [ "${TEST_MODE}" -eq 0 ] \
    && ! { systemctl is-active --quiet "${CRON_SERVICE}" && systemctl is-enabled --quiet "${CRON_SERVICE}"; }; then
    info "${CRON_SERVICE} is not running or does not start at boot, enabling and starting it"
    systemctl enable --now "${CRON_SERVICE}" \
        || info "WARNING: could not start ${CRON_SERVICE}. The scheduler will not run until it is: systemctl enable --now ${CRON_SERVICE}"
fi

info "done. The scheduler runs once a minute as ${WEB_USER} from ${CRON_TARGET}."
