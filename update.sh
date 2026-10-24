#!/usr/bin/env bash
#
# Upgrades rConfig V8 Core in place, in a fixed and tested order.
#
# Run as root, from anywhere:   sudo /var/www/html/rconfig/update.sh
#
# The script always works on the folder it lives in, and refuses to run if
# that folder is not an rConfig V8 Core install.
#
# Options:
#   --no-pull            do not run git pull. Use this when you check out a
#                        tag yourself, or to re-run after fixing a failed step.
#   --skip-redis-secure  do not set a Redis password, even if Redis has none.
#
# Order, and why:
#   1. Checks, then maintenance mode and Horizon stopped, before any code
#      changes, so no worker runs old code while files change under it.
#   2. git pull, then the script starts itself again so the rest of the run
#      uses the new version of this file.
#   3. composer before migrate, so migrations run against the new vendor code.
#   4. git and composer run as the owner of the app folder. artisan runs as
#      the web user. Root only writes /etc and manages services, so the run
#      does not leave root-owned files behind.
#   5. Ownership is fixed after the last step that runs artisan as root (the
#      two setup scripts), and Horizon only starts after that.
#
# If a step fails before any code has changed, the app is brought back up and
# Horizon is restarted. After that, the app stays in maintenance mode with
# Horizon stopped, because serving half upgraded code against a half migrated
# database is worse than a short outage. Fix the cause and run the script
# again with --no-pull: every step is safe to repeat.
#
# Optional environment, used by the test suite:
#   RCONFIG_OS_RELEASE            os-release file to detect the OS from.
#                                 Defaults to /etc/os-release.
#   RCONFIG_UPDATE_LOG_DIR        where the run log goes. Defaults to /var/log.
#   RCONFIG_UPDATE_LOCK           lock file that stops two runs at once.
#                                 Defaults to /run/rconfig-core-update.lock.
#   RCONFIG_PROC_DIR              proc filesystem to read processes from.
#                                 Defaults to /proc.
#   RCONFIG_UPDATE_KEEP_PATH      set to 1 to leave PATH exactly as given,
#                                 so only the test suite's fake binaries run.
#   RCONFIG_HORIZON_START_WAIT    seconds to wait for Horizon to report
#                                 RUNNING. Defaults to 30.

set -Eeuo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)"
APP_DIR_LOGICAL="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -L)"
SCRIPT_NAME="$(basename "${BASH_SOURCE[0]}")"

PROGRAM=rconfig-core-horizon
LEGACY_PROGRAM=horizon
PROC_DIR="${RCONFIG_PROC_DIR:-/proc}"
OS_RELEASE="${RCONFIG_OS_RELEASE:-/etc/os-release}"
LOG_DIR="${RCONFIG_UPDATE_LOG_DIR:-/var/log}"
LOCK_FILE="${RCONFIG_UPDATE_LOCK:-/run/rconfig-core-update.lock}"
HORIZON_START_WAIT="${RCONFIG_HORIZON_START_WAIT:-30}"

# State used by the failure handler.
MAIN_PID="${BASHPID}"
CURRENT_STEP="checks"
CAN_ROLL_BACK=0
APP_IS_UP=0
PUT_DOWN=0
WAS_DOWN=0
declare -a STOPPED_PROGRAMS=()
WORK_DIR=""

info() {
    echo "update: $*"
}

warn() {
    echo "update: WARNING: $*" >&2
}

step() {
    CURRENT_STEP="$1"
    echo
    echo "update: ==> $1"
}

# Prints what went wrong and what to do, puts things back where that is safe,
# then exits. Runs for an explicit fail and for any command that fails under
# set -e.
on_error() {
    local status="$1"

    trap - ERR

    # A failing command substitution runs this in the subshell too. Only the
    # main process reports and rolls back, so nothing happens twice.
    if [ "${BASHPID}" != "${MAIN_PID}" ]; then
        exit "${status}"
    fi

    echo >&2
    echo "update: ERROR: the step '${CURRENT_STEP}' failed (exit ${status})." >&2

    if [ "${CAN_ROLL_BACK}" -eq 1 ]; then
        roll_back
        echo "update: Nothing was changed. Fix the error above and run: sudo ./${SCRIPT_NAME}" >&2
    elif [ "${APP_IS_UP}" -eq 1 ]; then
        echo "update: The upgrade itself finished and the app is up, but this check failed. Fix the error above, then run this step again or re-run: sudo ./${SCRIPT_NAME} --no-pull" >&2
    else
        cat >&2 <<EOF
update: The app is left in maintenance mode and Horizon is stopped, because the
update: code, database and caches may be half upgraded.
update: Fix the error above, then finish the upgrade with:
update:     cd ${APP_DIR} && sudo ./${SCRIPT_NAME} --no-pull
update: It is safe to run again. To bring the app back without finishing:
update:     php artisan up && supervisorctl start ${PROGRAM}
EOF
    fi

    if [ -n "${LOG_FILE:-}" ]; then
        echo "update: Full log: ${LOG_FILE}" >&2
    fi

    exit 1
}

fail() {
    echo "update: ERROR: $*" >&2
    on_error 1
}

# Undoes the steps that ran before any code changed: restarts what was
# stopped and takes the app out of maintenance mode, unless it was already in
# maintenance mode before this run.
roll_back() {
    local program

    for program in "${STOPPED_PROGRAMS[@]}"; do
        supervisorctl start "${program}" || echo "update: could not restart ${program}, start it with: supervisorctl start ${program}" >&2
    done

    if [ "${PUT_DOWN}" -eq 1 ] && [ "${WAS_DOWN}" -eq 0 ]; then
        php "${APP_DIR}/artisan" up || echo "update: could not leave maintenance mode, run: php artisan up" >&2
    fi
}

# shellcheck disable=SC2317 # Runs from the EXIT trap.
cleanup() {
    if [ -n "${WORK_DIR}" ] && [ -d "${WORK_DIR}" ]; then
        rm -rf "${WORK_DIR}"
    fi
}

usage() {
    sed -n '3,16p' "${APP_DIR}/${SCRIPT_NAME}" | sed 's/^# \{0,1\}//'
}

# --- Helpers ---------------------------------------------------------------

os_release_value() {
    sed -n "s/^$1=//p" "${OS_RELEASE}" | tail -n 1 \
        | sed -e 's/^"\(.*\)"$/\1/' -e "s/^'\\(.*\\)'\$/\\1/" | tr '[:upper:]' '[:lower:]'
}

# State of a Supervisor program, for example RUNNING or STOPPED, or empty when
# Supervisor does not know it. status exits non-zero whenever any program is
# not running, so its exit code means nothing here.
program_state() {
    { supervisorctl status 2>/dev/null || true; } | awk -v name="$1" '$1 == name { print $2; exit }'
}

is_active_state() {
    case "$1" in
        RUNNING | STARTING | BACKOFF) return 0 ;;
        *) return 1 ;;
    esac
}

# The arguments of a process, one per line, or nothing if it is gone.
process_args() {
    if [ -r "${PROC_DIR}/$1/cmdline" ]; then
        tr '\0' '\n' <"${PROC_DIR}/$1/cmdline"
    fi
}

# Whether an artisan path belongs to this install, by physical or logical path.
is_this_artisan() {
    [ "$1" = "${APP_DIR}/artisan" ] || [ "$1" = "${APP_DIR_LOGICAL}/artisan" ]
}

# Whether the process runs this install's artisan.
runs_this_app() {
    local arg
    while IFS= read -r arg; do
        if is_this_artisan "${arg}"; then
            return 0
        fi
    done < <(process_args "$1")
    return 1
}

# Whether a Horizon master from another app folder is running, for example
# rConfig PRO on a shared box. Matches "<path>/artisan horizon" only, never
# horizon:work or other subcommands.
other_app_horizon_running() {
    local cmdline previous arg
    for cmdline in "${PROC_DIR}"/[0-9]*/cmdline; do
        [ -r "${cmdline}" ] || continue
        previous=""
        while IFS= read -r arg; do
            if [ "${arg}" = "horizon" ] && [[ "${previous}" == */artisan ]] && ! is_this_artisan "${previous}"; then
                return 0
            fi
            previous="${arg}"
        done < <(tr '\0' '\n' <"${cmdline}")
    done
    return 1
}

as_code_owner() {
    if [ "${CODE_OWNER}" = "root" ]; then
        env HOME=/root COMPOSER_ALLOW_SUPERUSER=1 "$@"
    else
        runuser -u "${CODE_OWNER}" -- env HOME="${CODE_OWNER_HOME}" "$@"
    fi
}

as_web_user() {
    runuser -u "${WEB_USER}" -- env HOME="${WEB_HOME}" "$@"
}

artisan() {
    as_web_user php "${APP_DIR}/artisan" "$@"
}

# Hands every file under storage and bootstrap/cache that is not already the
# web user's to the web user. Only those files are touched, so a re-run, or a
# box with hundreds of thousands of stored configs, does no needless work.
# chown -h changes a symlink itself, never the file it points to.
fix_ownership_once() {
    local dir status=0
    for dir in storage bootstrap/cache; do
        if [ -d "${APP_DIR}/${dir}" ]; then
            find "${APP_DIR}/${dir}" \( ! -user "${WEB_USER}" -o ! -group "${WEB_GID}" \) \
                -exec chown -h "${WEB_USER}:${WEB_GID}" {} + || status=1
        fi
    done
    return "${status}"
}

# find also fails when a file disappears while it walks, for example a
# rotated log, so one failure gets a second pass before the run stops.
fix_ownership() {
    if ! fix_ownership_once; then
        warn "some files could not be handed to ${WEB_USER}, trying once more"
        fix_ownership_once || fail "could not give storage and bootstrap/cache to ${WEB_USER}, see the errors above."
    fi
}

# --- The run ---------------------------------------------------------------

main() {
    local no_pull=0 skip_redis=0 resumed=0 arg
    local -a pass_on=()

    for arg in "$@"; do
        case "${arg}" in
            --no-pull) no_pull=1 ;;
            --skip-redis-secure) skip_redis=1; pass_on+=("${arg}") ;;
            --resumed) resumed=1 ;;
            -h | --help) usage; exit 0 ;;
            *) fail "unknown option '${arg}'. Run ./${SCRIPT_NAME} --help for the options." ;;
        esac
    done

    trap 'on_error $?' ERR
    trap cleanup EXIT

    # A run started by the old copy of this script, after git pull. Nothing
    # can be rolled back from here, since the code has already changed.
    if [ "${resumed}" -eq 1 ]; then
        WAS_DOWN="${RCONFIG_UPDATE_WAS_DOWN:-0}"
    else
        CAN_ROLL_BACK=1
    fi

    # --- Checks ------------------------------------------------------------

    step "checks"

    # pip installs supervisorctl and composer usually lives here, and sudo's
    # secure_path leaves both out.
    if [ "${RCONFIG_UPDATE_KEEP_PATH:-0}" != "1" ]; then
        PATH="${PATH}:/usr/local/sbin:/usr/local/bin"
    fi

    if [ "$(id -u)" -ne 0 ]; then
        fail "this script stops and starts services and writes to /etc, so it must be run as root, for example: sudo ./${SCRIPT_NAME}"
    fi

    if [ "${IS_DOCKER:-}" = "true" ]; then
        fail "this is a Docker container. Upgrade the Docker install by pulling the new image instead."
    fi

    local required
    for required in artisan composer.json .env horizon_supervisor.ini setup_supervisor.sh setup_cron.sh; do
        [ -e "${APP_DIR}/${required}" ] \
            || fail "${APP_DIR} is not an rConfig V8 Core install (${required} is missing). Run the copy of ${SCRIPT_NAME} inside the app folder."
    done
    grep -qE '"description"[[:space:]]*:[[:space:]]*"rConfig V8 Core' "${APP_DIR}/composer.json" \
        || fail "${APP_DIR} is not an rConfig V8 Core install (composer.json does not describe rConfig V8 Core). This script only upgrades Core."
    if [ "${no_pull}" -eq 0 ] && [ ! -d "${APP_DIR}/.git" ]; then
        fail "${APP_DIR} is not a git checkout, so it cannot be pulled. Update the files yourself, then run: sudo ./${SCRIPT_NAME} --no-pull"
    fi

    [ -r "${OS_RELEASE}" ] || fail "cannot read ${OS_RELEASE}, so the OS cannot be detected."
    local os_id os_id_like os_family="" word
    local -a os_words
    os_id="$(os_release_value ID)"
    os_id_like="$(os_release_value ID_LIKE)"
    read -ra os_words <<<"${os_id} ${os_id_like}"
    for word in "${os_words[@]}"; do
        case "${word}" in
            rhel | centos | rocky | almalinux | ol | amzn) os_family=rhel; break ;;
            ubuntu | debian) os_family=debian; break ;;
        esac
    done

    local apache_service supervisor_service
    case "${os_family}" in
        rhel) apache_service=httpd; supervisor_service=supervisord ;;
        debian) apache_service=apache2; supervisor_service=supervisor ;;
        *) fail "unsupported OS (ID=${os_id:-none}, ID_LIKE=${os_id_like:-none} in ${OS_RELEASE}). Supported: Rocky, AlmaLinux, RHEL and CentOS 8 or 9, Ubuntu 22.04 or later, and Amazon Linux 2023." ;;
    esac

    local tool
    for tool in php composer git supervisorctl systemctl runuser flock find getent stat chown; do
        if [ "${tool}" = "git" ] && [ "${no_pull}" -eq 1 ]; then
            continue
        fi
        command -v "${tool}" >/dev/null 2>&1 || fail "required command ${tool} not found. Install it and run this script again."
    done

    # A resumed run inherits the lock from the run that started it, on fd 9,
    # so no other run can slip in between the two.
    if [ "${resumed}" -eq 0 ]; then
        exec 9>>"${LOCK_FILE}"
    fi
    flock -n 9 || fail "another ${SCRIPT_NAME} run is in progress (lock ${LOCK_FILE})."

    # Everything from here on also goes to a log. A resumed run appends to the
    # log the first run opened.
    if [ -z "${RCONFIG_UPDATE_LOG_FILE:-}" ]; then
        LOG_FILE="${LOG_DIR}/rconfig-core-update-$(date +%Y%m%d%H%M%S).log"
        (umask 077 && : >>"${LOG_FILE}")
        export RCONFIG_UPDATE_LOG_FILE="${LOG_FILE}"
        exec > >(tee -a "${LOG_FILE}" 9>&-) 2>&1
    else
        LOG_FILE="${RCONFIG_UPDATE_LOG_FILE}"
    fi

    # The owner of the code. git and composer run as this user, so they never
    # leave root-owned files in the app folder and git does not refuse a
    # folder owned by someone else.
    local owner_ref="${APP_DIR}/composer.json"
    if [ -d "${APP_DIR}/.git" ]; then
        owner_ref="${APP_DIR}/.git"
    fi
    CODE_OWNER="$(stat -c '%U' "${owner_ref}")"
    local owner_entry
    owner_entry="$(getent passwd "${CODE_OWNER}")" \
        || fail "${owner_ref} is owned by a user with no name (${CODE_OWNER}). Give the app folder to the web user first."
    CODE_OWNER_HOME="$(printf '%s' "${owner_entry}" | cut -d: -f6)"

    info "app folder: ${APP_DIR}"
    info "OS: ${os_id} (${os_family} family)"
    info "code owner: ${CODE_OWNER}"
    info "log: ${LOG_FILE}"

    if [ "${resumed}" -eq 0 ]; then
        if [ -e "${APP_DIR}/storage/framework/down" ]; then
            WAS_DOWN=1
            info "the app was already in maintenance mode, and will be left that way"
        fi
    fi

    # --- Maintenance mode, best effort -------------------------------------

    # Best effort and as root, since the web user is only known for certain
    # once composer has run. On the first upgrade after a manual git pull the
    # code and vendor folder can be out of step and artisan may fail here. The
    # required maintenance step runs again after composer.
    step "maintenance mode"
    if php "${APP_DIR}/artisan" down; then
        PUT_DOWN=1
    else
        warn "could not enter maintenance mode yet, it is retried after composer install."
    fi

    # --- Stop Horizon --------------------------------------------------------

    step "stop Horizon"
    local state
    state="$(program_state "${PROGRAM}")"
    if is_active_state "${state}"; then
        info "stopping ${PROGRAM}. This waits for running jobs to finish."
        supervisorctl stop "${PROGRAM}"
        STOPPED_PROGRAMS+=("${PROGRAM}")
    else
        info "${PROGRAM} is not running"
    fi

    # Older Core releases used the program name horizon, which rConfig PRO
    # also uses. It is only stopped when its process runs this app's artisan.
    state="$(program_state "${LEGACY_PROGRAM}")"
    if is_active_state "${state}"; then
        local pid
        pid="$(supervisorctl pid "${LEGACY_PROGRAM}" 2>/dev/null || true)"
        if [[ "${pid}" =~ ^[1-9][0-9]*$ ]] && runs_this_app "${pid}"; then
            info "stopping the old ${LEGACY_PROGRAM} program, which runs this install"
            supervisorctl stop "${LEGACY_PROGRAM}"
            STOPPED_PROGRAMS+=("${LEGACY_PROGRAM}")
        else
            info "leaving the ${LEGACY_PROGRAM} program alone, it does not run ${APP_DIR}"
        fi
    fi

    # --- git pull, then start again from the new script ---------------------

    if [ "${no_pull}" -eq 0 ]; then
        step "git pull"
        as_code_owner git -C "${APP_DIR}" pull --ff-only \
            || fail "git pull failed. If you have local changes, run 'git stash' first. If you checked out a tag, run with --no-pull."

        CAN_ROLL_BACK=0
        info "starting the updated ${SCRIPT_NAME}"
        export RCONFIG_UPDATE_WAS_DOWN="${WAS_DOWN}"
        exec bash "${APP_DIR}/${SCRIPT_NAME}" --no-pull --resumed "${pass_on[@]}"
    fi

    # From here on the code may already have changed, so nothing rolls back.
    CAN_ROLL_BACK=0

    # --- composer ------------------------------------------------------------

    # --no-scripts so composer does not run artisan as the code owner before
    # ownership is fixed. package:discover runs as the web user below.
    step "composer install"
    WORK_DIR="$(mktemp -d)"
    if [ "${CODE_OWNER}" != "root" ]; then
        chown "${CODE_OWNER}" "${WORK_DIR}"
    fi
    as_code_owner COMPOSER_HOME="${WORK_DIR}" composer install --no-dev --no-interaction --no-scripts \
        --optimize-autoloader --working-dir="${APP_DIR}"

    # composer's post-autoload-dump script removes these, and --no-scripts
    # skips it. A stale package manifest still names providers --no-dev just
    # removed, such as ide-helper, and artisan would not boot at all.
    rm -f "${APP_DIR}/bootstrap/cache/packages.php" "${APP_DIR}/bootstrap/cache/services.php" \
        "${APP_DIR}/bootstrap/cache/config.php"

    # --- Web user ------------------------------------------------------------

    step "detect the web user"
    local web_user_output passwd_entry web_uid
    web_user_output="$(php "${APP_DIR}/artisan" rconfig:web-user)" \
        || fail "could not detect the web user: 'php artisan rconfig:web-user' failed."
    WEB_USER="$(printf '%s\n' "${web_user_output}" | tail -n 1 | tr -d '[:space:]')"
    [[ "${WEB_USER}" =~ ^[a-z_][a-z0-9_-]*$ ]] \
        || fail "artisan rconfig:web-user returned '${WEB_USER}', which is not a valid user name."
    passwd_entry="$(getent passwd "${WEB_USER}")" \
        || fail "web user '${WEB_USER}' does not exist on this host. Install and start the web server (httpd or apache2) first."
    web_uid="$(printf '%s' "${passwd_entry}" | cut -d: -f3)"
    [ "${web_uid}" != "0" ] || fail "web user '${WEB_USER}' has uid 0. rConfig must never run as root."
    WEB_GID="$(printf '%s' "${passwd_entry}" | cut -d: -f4)"
    WEB_HOME="$(printf '%s' "${passwd_entry}" | cut -d: -f6)"
    info "web user: ${WEB_USER}"

    # Root-owned files left by older releases would stop artisan running as
    # the web user, so ownership is fixed once now and again at the end.
    step "fix ownership"
    fix_ownership

    step "rebuild the package manifest"
    artisan optimize:clear
    artisan package:discover

    step "maintenance mode"
    artisan down

    # --- Database ------------------------------------------------------------

    step "migrate"
    artisan migrate --force
    artisan rconfig:sync-tasks

    # --- Redis ---------------------------------------------------------------

    # --no-restart is safe because Horizon is stopped and the caches are
    # rebuilt from the new .env below. Skipped when another app's Horizon is
    # running, since a box shared with rConfig PRO may share one Redis and a
    # new password would cut that app off.
    step "secure Redis"
    if [ "${skip_redis}" -eq 1 ]; then
        info "skipped (--skip-redis-secure)"
    elif other_app_horizon_running; then
        warn "skipped setting a Redis password: another app's Horizon is running on this box and may share this Redis. Set one by hand once both apps are ready: php artisan rconfig:redis-secure"
    elif ! artisan rconfig:redis-secure --no-restart; then
        warn "rconfig:redis-secure failed, see above. The upgrade carries on. Check with: php artisan rconfig:redis-security-check"
    fi

    # --- Supervisor and cron -------------------------------------------------

    # Both scripts run artisan as root, so they come before the final
    # ownership fix. RCONFIG_SUPERVISOR_DEFER_START makes setup_supervisor.sh
    # write and reread the config without starting Horizon.
    step "Supervisor config"
    RCONFIG_SUPERVISOR_DEFER_START=1 bash "${APP_DIR}/setup_supervisor.sh"

    step "scheduler cron file"
    bash "${APP_DIR}/setup_cron.sh"

    # --- Caches --------------------------------------------------------------

    step "rebuild caches"
    artisan optimize

    # --- Ownership, then start ----------------------------------------------

    step "fix ownership"
    fix_ownership

    step "start Horizon"
    if ! systemctl is-active --quiet "${supervisor_service}"; then
        systemctl start "${supervisor_service}"
    fi
    # Applies the config setup_supervisor.sh wrote: removes the old program
    # and adds ${PROGRAM}, starting it if it is new.
    supervisorctl update
    if [ "$(program_state "${PROGRAM}")" != "RUNNING" ]; then
        supervisorctl start "${PROGRAM}"
    fi
    local waited=0
    until [ "$(program_state "${PROGRAM}")" = "RUNNING" ]; do
        if [ "${waited}" -ge "${HORIZON_START_WAIT}" ]; then
            fail "${PROGRAM} did not reach RUNNING within ${HORIZON_START_WAIT} seconds. Check: supervisorctl status ${PROGRAM}, and /var/log/rconfig-core-horizon.log"
        fi
        sleep 1
        waited=$((waited + 1))
    done
    info "${PROGRAM} is running"

    # --- Web server ----------------------------------------------------------

    # PHP under php-fpm keeps its opcache across an Apache restart, so any
    # active php-fpm unit is restarted too.
    step "restart the web server"
    if systemctl cat "${apache_service}.service" >/dev/null 2>&1; then
        systemctl restart "${apache_service}"
    else
        warn "no ${apache_service}.service found. Restart your web server yourself."
    fi
    local fpm_unit
    while IFS= read -r fpm_unit; do
        if [ -n "${fpm_unit}" ]; then
            systemctl restart "${fpm_unit}"
        fi
    done < <(systemctl list-units --type=service --state=active --plain --no-legend 'php*fpm*.service' 2>/dev/null | awk '{ print $1 }')

    step "leave maintenance mode"
    if [ "${WAS_DOWN}" -eq 1 ]; then
        info "left in maintenance mode, as it was before this run. Run 'php artisan up' when ready."
    else
        artisan up
    fi
    APP_IS_UP=1

    # --- After the app is back -------------------------------------------------

    # Only chmods configs written by older releases, and can take minutes on a
    # big install, so it runs after the app is back up.
    step "config file permissions"
    artisan rconfig:set-config-permissions

    step "check the worker user"
    if artisan list --raw | awk '$1 == "rconfig:check-worker-user" { found = 1 } END { exit !found }'; then
        artisan rconfig:check-worker-user
    else
        info "NOTICE: rconfig:check-worker-user is not in this release yet (RCO-1801), so the worker user was not checked. supervisorctl reports ${PROGRAM} as RUNNING."
    fi

    echo
    info "done. rConfig V8 Core is upgraded."
}

main "$@"; exit
