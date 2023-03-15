import { ref, reactive } from 'vue'

function createUUID() {
    let dt = new Date().getTime();
    var uuid = "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(
      /[xy]/g,
      function (c) {
        var r = (dt + Math.random() * 16) % 16 | 0;
        dt = Math.floor(dt / 16);
        return (c == "x" ? r : (r & 0x3) | 0x8).toString(16);
      }
    );
    return uuid;
  }

const defaultNotificationOptions = {
    type: "info", // error, warning, info, success
    title: "Info Notification",
    message: "Ooops! A message was not provided",
    autoClose: true,
    duration: 3,
  };

// see example here https://github.com/zafaralam/vue-3-toast/

export default function useNotifications() {
    const notifications = ref([]);

    const createNotification = (options) => {
        
        const _options = Object.assign({ ...defaultNotificationOptions }, options);

        notifications.value.push(
          ...[
            {
              id: createUUID(),
              ..._options,
            },
          ]
        );
      };
  
      const removeNotifications = (id) => {
        const index = notifications.value.findIndex((item) => item.id === id);
        if (index !== -1) notifications.value.splice(index, 1);
      };

    return {
        notifications,
        createNotification,
        removeNotifications
    };
  };
  