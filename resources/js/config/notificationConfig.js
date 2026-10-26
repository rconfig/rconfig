export const notificationConfig = {
    notifications: {
        categories: {
            system: {
                label: "System Events",
                description: "General system notifications and alerts",
            },
            config: {
                label: "Configuration Changes",
                description: "Configuration downloads, changes, and management",
            },
            connection: {
                label: "Device Connection Issues",
                description: "Device connection failures and network issues",
            },
            task: {
                label: "Task Completion",
                description: "Task completion notifications and reports",
            },
        },
        channels: {
            db: {
                label: "In-App",
                description: "Notifications shown in your dashboard",
            },
            mail: {
                label: "Email",
                description: "Sent to your email address",
            },
        },
        types: {
            // System
            system_notification_error: {
                label: "Notification System Error",
                description: "Alerts when notification system encounters errors",
            },
            system_health_check_failed: {
                label: "Health Check Failed",
                description: "Alerts when a system health check (database, Redis, Horizon, disk space, etc.) starts failing",
            },
            system_health_check_recovered: {
                label: "Health Check Recovered",
                description: "Notifications when a failing system health check passes again",
            },
            // Config
            config_download_completed: {
                label: "Configuration Download Completed",
                description: "Notifications when manual config downloads complete",
            },
            config_purge_completed: {
                label: "Configuration Purge Completed",
                description: "Alerts when configuration purge operations finish",
            },
            config_purge_failed_completed: {
                label: "Failed Configuration Purge Completed",
                description: "Notifications when failed config purge operations complete",
            },
            config_changed: {
                label: "Configuration Changed",
                description: "Email alerts when a device configuration changes between versions",
            },
            // Connection
            connection_device_failure: {
                label: "Device Connection Failure",
                description: "Alerts when devices fail to connect via SSH, Telnet, or API",
            },
            // Task
            task_completed: {
                label: "Task Completed",
                description: "Notifications when scheduled tasks complete",
            },
            task_download_report: {
                label: "Download Task Report",
                description: "Email reports for download task completion",
            },
        },
    },
};