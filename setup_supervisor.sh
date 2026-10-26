#!/usr/bin/env bash
#
# Renders the rConfig Supervisor stubs into the Supervisor config folder, and
# writes /etc/logrotate.d/rconfig-core-horizon to rotate the Horizon log.
#
# The rendered files are regular files owned by root:root with mode 0644. They
# are never symlinks into the app folder: the web user owns that folder, and
# root's supervisord loads whatever is in it, so a link there would let the
# web user run any command as root.
#
# Run as root, from the rConfig app folder:   ./setup_supervisor.sh
#
# Safe to run more than once. Every check runs before anything is written, and
# the script exits non-zero without touching Supervisor if any check fails.
# This matters because supervisord rejects an unknown user for every program
# it manages: a bad file stops reread and update for the whole host, and stops
# supervisord starting at all on the next boot.
#
# Optional environment, used by the test suite:
#   RCONFIG_SUPERVISOR_DIR  render into this folder instead of the OS default.
#                           Supervisor is not reloaded and restorecon is not run.
#   RCONFIG_SUPERVISOR_EXT  file extension to use with RCONFIG_SUPERVISOR_DIR
#                           (ini or conf). Defaults to the OS default.
#   RCONFIG_LOGROTATE_DIR   with RCONFIG_SUPERVISOR_DIR, write the logrotate
#                           file into this folder. Without it, test mode writes
#                           no logrotate file. Ignored outside test mode, which
#                           always uses /etc/logrotate.d.
#   RCONFIG_PHP             PHP binary used to run artisan. Defaults to php.
#   RCONFIG_OS_RELEASE      os-release file to detect the OS from. Defaults to
#                           /etc/os-release.
#   RCONFIG_SUPERVISOR_DEFER_START
#                           set to 1 to write the config and reread it, but not
#                           start Horizon or supervisord. update.sh uses this
#                           so Horizon only starts once ownership is fixed; it
#                           then runs supervisorctl update itself.
#   RCONFIG_SUPERVISOR_TEST_RELOAD
#                           set to 1 with RCONFIG_SUPERVISOR_DIR to run the
#                           reload steps too. Only for tests that put fake
#                           supervisorctl and systemctl first on PATH.

set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)"
STUBS=(horizon_supervisor)
PHP_BIN="${RCONFIG_PHP:-php}"

# Stub name to the file name it is written under. CORE uses its own name
# because rConfig PRO writes horizon_supervisor.ini into the same folder, and
# a box can run both.
declare -A TARGET_NAMES=([horizon_supervisor]=rconfig-core-horizon)

# Names older CORE releases linked the stub in under. Only removed when they
# provably belong to this install, since PRO uses the same names.
LEGACY_NAMES=(horizon_supervisor.ini horizon_supervisor.conf)

fail() {
    echo "setup_supervisor: ERROR: $*" >&2
    exit 1
}

info() {
    echo "setup_supervisor: $*"
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
        OS_SUPDIR=/etc/supervisord.d
        OS_EXT=ini
        SUPERVISOR_SERVICE=supervisord
        SUPERVISOR_INSTALL="Install it from EPEL (dnf install epel-release, or on RHEL the epel-release RPM from dl.fedoraproject.org), then: dnf install supervisor"
        ;;
    debian)
        OS_SUPDIR=/etc/supervisor/conf.d
        OS_EXT=conf
        SUPERVISOR_SERVICE=supervisor
        SUPERVISOR_INSTALL="Install it with: apt install supervisor"
        ;;
    *)
        if [ "${OS_ID}" = "amzn" ]; then
            fail "Amazon Linux is no longer supported from 8.2.19. Stay on your current version and contact support. Supported: Rocky, AlmaLinux, RHEL and CentOS 8 or 9, and Ubuntu 22.04 or later."
        fi
        fail "unsupported OS (ID=${OS_ID:-none}, ID_LIKE=${OS_ID_LIKE:-none} in ${OS_RELEASE}). Supported: Rocky, AlmaLinux, RHEL and CentOS 8 or 9, and Ubuntu 22.04 or later."
        ;;
esac

info "OS: ${OS_ID} (${OS_FAMILY} family), Supervisor service ${SUPERVISOR_SERVICE}"

# --- Work out the Supervisor config folder ----------------------------------

if [ -n "${RCONFIG_SUPERVISOR_DIR:-}" ]; then
    TEST_MODE=1
    SUPDIR="${RCONFIG_SUPERVISOR_DIR}"
    EXT="${RCONFIG_SUPERVISOR_EXT:-${OS_EXT}}"
    LOGROTATE_DIR="${RCONFIG_LOGROTATE_DIR:-}"
else
    TEST_MODE=0
    SUPDIR="${OS_SUPDIR}"
    EXT="${OS_EXT}"
    LOGROTATE_DIR=/etc/logrotate.d

    if [ "$(id -u)" -ne 0 ]; then
        fail "this script writes to the Supervisor config folder and must be run as root, for example: sudo ./setup_supervisor.sh"
    fi
fi

case "${EXT}" in
    ini | conf) ;;
    *) fail "unsupported Supervisor config extension '${EXT}'. Use ini or conf." ;;
esac

if [ ! -d "${SUPDIR}" ]; then
    fail "Supervisor config folder ${SUPDIR} not found, so Supervisor is not installed. ${SUPERVISOR_INSTALL}"
fi

if [ "${TEST_MODE}" -eq 0 ]; then
    command -v supervisorctl >/dev/null 2>&1 \
        || fail "supervisorctl not found, so Supervisor is not installed. ${SUPERVISOR_INSTALL}"
    systemctl cat "${SUPERVISOR_SERVICE}.service" >/dev/null 2>&1 \
        || fail "no ${SUPERVISOR_SERVICE}.service systemd unit found. ${SUPERVISOR_INSTALL}"
fi

for tool in cmp getent awk; do
    command -v "${tool}" >/dev/null 2>&1 \
        || fail "required command ${tool} not found. Install it (cmp is in the diffutils package) and run this script again."
done

# The app folder goes into command=, inside a single quoted sh -c string.
# Supervisor splits command= on whitespace, a quote would end the sh -c
# string, and anything outside this set is more likely a mistake than a real
# install path.
if [[ ! "${APP_DIR}" =~ ^/[A-Za-z0-9._/-]+$ ]]; then
    fail "app folder '${APP_DIR}' contains characters that cannot be written into a Supervisor config safely."
fi

# --- Detect and validate the web user ---------------------------------------

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
    fail "web user '${WEB_USER}' has uid 0. Horizon must never run as root."
fi

WEB_GID="$(printf '%s' "${PASSWD_ENTRY}" | cut -d: -f4)"
if ! WEB_GROUP_ENTRY="$(getent group "${WEB_GID}")"; then
    fail "the primary group (gid ${WEB_GID}) of web user '${WEB_USER}' does not exist on this host."
fi
WEB_GROUP="$(printf '%s' "${WEB_GROUP_ENTRY}" | cut -d: -f1)"
if [[ ! "${WEB_GROUP}" =~ ^[a-z_][a-z0-9_-]*$ ]]; then
    fail "the primary group of web user '${WEB_USER}' is '${WEB_GROUP}', which is not a valid group name."
fi

info "web user: ${WEB_USER}"
info "app folder: ${APP_DIR}"
info "Supervisor config folder: ${SUPDIR}"

# --- Render every stub in memory and check it -------------------------------

declare -A RENDERED

for stub in "${STUBS[@]}"; do
    stub_path="${APP_DIR}/${stub}.ini"
    [ -f "${stub_path}" ] || fail "stub ${stub_path} not found."

    content="$(<"${stub_path}")"
    # The replacement is quoted so bash 5.2 does not treat & in it as the
    # matched text.
    content="${content//RCONFIG_WEB_USER/"${WEB_USER}"}"
    content="${content//RCONFIG_APP_DIR/"${APP_DIR}"}"

    if leftover="$(printf '%s\n' "${content}" | grep -nE 'RCONFIG_[A-Z0-9_]+|ENVOY_[A-Z0-9_]+')"; then
        fail "${stub}.ini still contains a placeholder after rendering, nothing was written:
${leftover}"
    fi

    if printf '%s\n' "${content}" | grep -qiE '^[[:space:]]*user[[:space:]]*=[[:space:]]*root[[:space:]]*$'; then
        fail "${stub}.ini renders with user=root, nothing was written."
    fi

    # Without a user= line supervisord runs the program as itself, which is root.
    if ! printf '%s\n' "${content}" | grep -qE "^user=${WEB_USER}\$"; then
        fail "${stub}.ini has no user=${WEB_USER} line after rendering, nothing was written."
    fi

    RENDERED["${stub}"]="${content}"
done

# --- Render the logrotate file and check it ---------------------------------

# Horizon appends to its log itself, so nothing else rotates it. copytruncate
# keeps Horizon's open file working, and su runs the rotation as the web user,
# which owns storage/logs. No backup of an older file is kept in
# logrotate.d: logrotate loads every file there, whatever its name, so a copy
# would rotate the log twice.
HORIZON_LOG="${APP_DIR}/storage/logs/horizon.log"
LOGROTATE_NAME=rconfig-core-horizon
LOGROTATE_CONTENT="${HORIZON_LOG} {
    su ${WEB_USER} ${WEB_GROUP}
    size 50M
    rotate 10
    compress
    missingok
    notifempty
    copytruncate
}"

if [[ "${RENDERED[horizon_supervisor]}" != *">> ${HORIZON_LOG} 2>&1'"* ]]; then
    fail "horizon_supervisor.ini does not write Horizon's log to ${HORIZON_LOG}, so the logrotate file would rotate the wrong file. Nothing was written."
fi

if [ -n "${LOGROTATE_DIR}" ] && [ ! -d "${LOGROTATE_DIR}" ]; then
    info "WARNING: ${LOGROTATE_DIR} not found, so logrotate is not installed and ${HORIZON_LOG} will not be rotated. Install logrotate and run this script again."
    LOGROTATE_DIR=""
elif [ -z "${LOGROTATE_DIR}" ]; then
    info "RCONFIG_LOGROTATE_DIR is not set in test mode, not writing a logrotate file"
fi

# --- Write the rendered files into place ------------------------------------

TMP_FILE=""
cleanup() {
    if [ -n "${TMP_FILE}" ] && [ -e "${TMP_FILE}" ]; then
        rm -f "${TMP_FILE}"
    fi
}
trap cleanup EXIT

for stub in "${STUBS[@]}"; do
    target="${SUPDIR}/${TARGET_NAMES[${stub}]}.${EXT}"

    # Created in the target folder so the final mv is an atomic rename on the
    # same filesystem, and the file picks up the folder's SELinux context.
    TMP_FILE="$(mktemp "${SUPDIR}/.${TARGET_NAMES[${stub}]}.XXXXXX")"
    printf '%s\n' "${RENDERED[${stub}]}" >"${TMP_FILE}"
    chmod 0644 "${TMP_FILE}"
    if [ "$(id -u)" -eq 0 ]; then
        chown root:root "${TMP_FILE}"
    fi

    # Keep a copy of a file that differs, so hand edits to the rendered file are
    # not lost. An old symlink into the app folder is skipped: its content is
    # the stub there, which this script never changes. The suffix keeps the
    # copy out of Supervisor's include pattern.
    points_into_app=0
    if [ -L "${target}" ]; then
        case "$(readlink -f "${target}" || true)" in
            "${APP_DIR}"/*) points_into_app=1 ;;
        esac
    fi
    if [ -e "${target}" ] && [ "${points_into_app}" -eq 0 ] && ! cmp -s "${target}" "${TMP_FILE}"; then
        backup="${target}.rconfig-bak-$(date +%Y%m%d%H%M%S)"
        cp -L "${target}" "${backup}"
        info "existing ${target} differed from the stub, saved a copy to ${backup}"
    fi

    if [ -L "${target}" ]; then
        info "replacing symlink ${target} -> $(readlink "${target}") with a regular file"
    fi

    # rename(2) replaces a symlink itself, never the file it points to, so the
    # stub in the app folder is left alone.
    mv -fT "${TMP_FILE}" "${target}"
    TMP_FILE=""
    info "wrote ${target}"

    if [ "${TEST_MODE}" -eq 0 ] && command -v restorecon >/dev/null 2>&1; then
        restorecon -F "${target}" || info "restorecon failed on ${target}, check its SELinux context"
    fi
done

if [ -n "${LOGROTATE_DIR}" ]; then
    target="${LOGROTATE_DIR}/${LOGROTATE_NAME}"
    TMP_FILE="$(mktemp "${LOGROTATE_DIR}/.${LOGROTATE_NAME}.XXXXXX")"
    printf '%s\n' "${LOGROTATE_CONTENT}" >"${TMP_FILE}"
    chmod 0644 "${TMP_FILE}"
    if [ "$(id -u)" -eq 0 ]; then
        chown root:root "${TMP_FILE}"
    fi
    mv -fT "${TMP_FILE}" "${target}"
    TMP_FILE=""
    info "wrote ${target}"

    if [ "${TEST_MODE}" -eq 0 ] && command -v restorecon >/dev/null 2>&1; then
        restorecon -F "${target}" || info "restorecon failed on ${target}, check its SELinux context"
    fi
fi

# Whether a Supervisor config file has a command= that runs something from
# this app folder. Matched per word, so /var/www/html/rconfig/ does not match
# a command under /srv/var/www/html/rconfig/ or /var/www/html/rconfig8/.
runs_from_app_dir() {
    awk -v dir="${APP_DIR}/" '
        /^[[:space:]]*command[[:space:]]*=/ {
            line = $0
            sub(/^[^=]*=/, "", line)
            n = split(line, words, /[[:space:]]+/)
            for (i = 1; i <= n; i++) {
                if (index(words[i], dir) == 1) { found = 1 }
            }
        }
        END { exit !found }
    ' "$1"
}

# Retire the old file names. Removed only when they belong to this install:
# a symlink into this app folder, or a regular file whose command= runs from
# it. Anything else, such as PRO's own horizon_supervisor.ini, is left alone.
for legacy in "${LEGACY_NAMES[@]}"; do
    old="${SUPDIR}/${legacy}"
    if [ -L "${old}" ]; then
        resolved="$(readlink -f "${old}" || true)"
        case "${resolved}" in
            "${APP_DIR}"/*)
                rm -f "${old}"
                info "removed old symlink ${old} -> ${resolved}"
                ;;
            *)
                info "note: left ${old} alone, it links to $(readlink "${old}"), not into ${APP_DIR}"
                ;;
        esac
    elif [ -f "${old}" ]; then
        if runs_from_app_dir "${old}"; then
            backup="${old}.rconfig-bak-$(date +%Y%m%d%H%M%S)"
            cp -p "${old}" "${backup}"
            rm -f "${old}"
            info "removed old ${old}, which ran this install. Saved a copy to ${backup}"
        else
            info "note: left ${old} alone, its command= does not run from ${APP_DIR}"
        fi
    fi
done

# Remove any other symlink in the folder that still points into this app
# folder, for example one an operator created by hand under another name.
for link in "${SUPDIR}"/*; do
    if [ -L "${link}" ]; then
        resolved="$(readlink -f "${link}" || true)"
        case "${resolved}" in
            "${APP_DIR}"/*)
                rm -f "${link}"
                info "removed old symlink ${link} -> ${resolved}"
                ;;
        esac
    fi
done

# A hand made file under some other name that also runs this install would
# start Horizon twice. It is not provably safe to remove, so only warn.
for other in "${SUPDIR}"/*."${EXT}"; do
    case "${other}" in
        "${SUPDIR}/rconfig-core-"*) continue ;;
    esac
    if [ -f "${other}" ] && [ ! -L "${other}" ] && runs_from_app_dir "${other}"; then
        info "WARNING: ${other} also runs a program from ${APP_DIR}. Remove it if it starts Horizon, or Horizon will run twice."
    fi
done

# --- Reload Supervisor ------------------------------------------------------

if [ "${TEST_MODE}" -eq 1 ] && [ "${RCONFIG_SUPERVISOR_TEST_RELOAD:-0}" != "1" ]; then
    info "RCONFIG_SUPERVISOR_DIR is set, not reloading Supervisor"
    exit 0
fi

DEFER_START="${RCONFIG_SUPERVISOR_DEFER_START:-0}"

# The EPEL and pip installs do not enable the service, so without this Horizon
# would stop at the next reboot.
if ! systemctl is-enabled --quiet "${SUPERVISOR_SERVICE}"; then
    info "${SUPERVISOR_SERVICE} does not start at boot, enabling it"
    systemctl enable "${SUPERVISOR_SERVICE}" || fail "could not enable ${SUPERVISOR_SERVICE}."
fi

# reread only loads and checks the new config. It never starts anything, so
# the caller decides when Horizon starts.
if [ "${DEFER_START}" = "1" ]; then
    if systemctl is-active --quiet "${SUPERVISOR_SERVICE}"; then
        supervisorctl reread || fail "supervisorctl reread failed, see the error above. The rendered files are in ${SUPDIR}."
    else
        info "${SUPERVISOR_SERVICE} is not running, leaving it stopped"
    fi
    info "RCONFIG_SUPERVISOR_DEFER_START is set, not starting Horizon. Apply the config with: supervisorctl update"
    exit 0
fi

# reread and update apply only the changed programs, so anything else this
# Supervisor runs keeps running. A full restart would bounce all of them.
if systemctl is-active --quiet "${SUPERVISOR_SERVICE}"; then
    supervisorctl reread || fail "supervisorctl reread failed, see the error above. The rendered files are in ${SUPDIR}."
    supervisorctl update || fail "supervisorctl update failed, see the error above."
else
    info "${SUPERVISOR_SERVICE} is not running, starting it"
    systemctl start "${SUPERVISOR_SERVICE}" || fail "could not start ${SUPERVISOR_SERVICE}."
fi

# status exits non-zero while a program is still starting, which is not an error here.
supervisorctl status || true
