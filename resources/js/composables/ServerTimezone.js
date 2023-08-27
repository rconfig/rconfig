import { reactive, toRefs } from 'vue';

const state = reactive({
    timezoneOffset: 0
});

export default function useServerTimezone(timezone) {
    const clientTimezone = timezone;

    function getLang() {
        if (navigator.languages != undefined) return navigator.languages[0];
        return navigator.language;
    }

    function formatTime(finished_at) {
        // if finished_at is not a valid date, return finished_at
        if (isNaN(Date.parse(finished_at))) {
            return finished_at;
        }

        var d = new Date(Date.parse(finished_at));

        let locale = getLang();

        const mediumTime = new Intl.DateTimeFormat(locale, {
            timeStyle: 'short',
            dateStyle: 'short'
        });

        var timeAgo = mediumTime.format(d);
        return timeAgo;
    }

    Date.prototype.addMinutes = function (minutes) {
        this.setMinutes(this.getMinutes() + minutes);
        return this;
    };

    return {
        formatTime,
        ...toRefs(state) // convert to refs when returning
    };
}
