<template>
  <div>
    <div
      class="pf-m-error"
      v-if="errors.minute || errors.hour || errors.day || errors.month || errors.weekday">
      <p
        class="pf-c-form__helper-text pf-m-error"
        id="form-help-text-info-helper"
        aria-live="polite">
        <span class="pf-c-form__helper-text-icon">
          <i
            class="fas fa-exclamation-circle"
            aria-hidden="true"></i>
        </span>
        Error Notice:
      </p>
      <p
        class="pf-c-form__helper-text pf-m-error pf-u-ml-lg"
        v-for="error in errors">
        {{ error }}
      </p>
    </div>

    <!--MINUTES-->
    <div class="pf-c-form__group pf-u-pt-md">
      <div class="pf-c-form__group-label">
        <label
          class="pf-c-form__label"
          for="form-demo-basic-name">
          <span class="pf-c-form__label-text">Minute</span>
          <span
            class="pf-c-form__label-required"
            aria-hidden="true">
            &#42;
          </span>
        </label>
      </div>
      <div class="pf-c-form__group-control pf-l-grid pf-m-gutter">
        <div class="pf-l-grid__item pf-m-3-col">
          <input
            type="text"
            name="minute"
            id="minute"
            v-model="cronReturnArray.minute"
            autocomplete="off"
            spellcheck="false"
            class="pf-c-form-control"
            @input="onChangeMin" />
        </div>
        <div class="pf-l-grid__item pf-m-9-col">
          <select
            id="minuteSelect"
            class="pf-c-form-control"
            v-model="cronReturnArray.minute"
            @change="onChangeMin">
            <option
              value="--"
              disabled>
              -- Select an option --
            </option>
            <option value="*">Every minute (*)</option>
            <option value="*/2">Every other minute (*/2)</option>
            <option value="*/5">Every 5 minutes (*/5)</option>
            <option value="*/10">Every 10 minutes (*/10)</option>
            <option value="*/15">Every 15 minutes (*/15)</option>
            <option value="0,30">Every 30 minutes (0,30)</option>
            <option
              value="--"
              disabled>
              -- Minutes --
            </option>
            <option value="0">:00 top of the hour (0)</option>
            <option value="1">:01 (1)</option>
            <option value="2">:02 (2)</option>
            <option value="3">:03 (3)</option>
            <option value="4">:04 (4)</option>
            <option value="5">:05 (5)</option>
            <option value="6">:06 (6)</option>
            <option value="7">:07 (7)</option>
            <option value="8">:08 (8)</option>
            <option value="9">:09 (9)</option>
            <option value="10">:10 (10)</option>
            <option value="11">:11 (11)</option>
            <option value="12">:12 (12)</option>
            <option value="13">:13 (13)</option>
            <option value="14">:14 (14)</option>
            <option value="15">:15 quarter past (15)</option>
            <option value="16">:16 (16)</option>
            <option value="17">:17 (17)</option>
            <option value="18">:18 (18)</option>
            <option value="19">:19 (19)</option>
            <option value="20">:20 (20)</option>
            <option value="21">:21 (21)</option>
            <option value="22">:22 (22)</option>
            <option value="23">:23 (23)</option>
            <option value="24">:24 (24)</option>
            <option value="25">:25 (25)</option>
            <option value="26">:26 (26)</option>
            <option value="27">:27 (27)</option>
            <option value="28">:28 (28)</option>
            <option value="29">:29 (29)</option>
            <option value="30">:30 half past (30)</option>
            <option value="31">:31 (31)</option>
            <option value="32">:32 (32)</option>
            <option value="33">:33 (33)</option>
            <option value="34">:34 (34)</option>
            <option value="35">:35 (35)</option>
            <option value="36">:36 (36)</option>
            <option value="37">:37 (37)</option>
            <option value="38">:38 (38)</option>
            <option value="39">:39 (39)</option>
            <option value="40">:40 (40)</option>
            <option value="41">:41 (41)</option>
            <option value="42">:42 (42)</option>
            <option value="43">:43 (43)</option>
            <option value="44">:44 (44)</option>
            <option value="45">:45 quarter til (45)</option>
            <option value="46">:46 (46)</option>
            <option value="47">:47 (47)</option>
            <option value="48">:48 (48)</option>
            <option value="49">:49 (49)</option>
            <option value="50">:50 (50)</option>
            <option value="51">:51 (51)</option>
            <option value="52">:52 (52)</option>
            <option value="53">:53 (53)</option>
            <option value="54">:54 (54)</option>
            <option value="55">:55 (55)</option>
            <option value="56">:56 (56)</option>
            <option value="57">:57 (57)</option>
            <option value="58">:58 (58)</option>
            <option value="59">:59 (59)</option>
          </select>
        </div>
      </div>
    </div>
    
    <!--HOURS-->
    <div class="pf-c-form__group pf-u-pt-md">
      <div class="pf-c-form__group-label">
        <label
          class="pf-c-form__label"
          for="form-demo-basic-name">
          <span class="pf-c-form__label-text">Hour</span>
          <span
            class="pf-c-form__label-required"
            aria-hidden="true">
            &#42;
          </span>
        </label>
      </div>
      <div class="pf-c-form__group-control pf-l-grid pf-m-gutter">
        <div class="pf-l-grid__item pf-m-3-col">
          <input
            type="text"
            name="hour"
            id="hour"
            v-model="cronReturnArray.hour"
            autocomplete="off"
            spellcheck="false"
            class="pf-c-form-control"
            @input="onChangeHour" />
        </div>
        <div class="pf-l-grid__item pf-m-9-col">
          <select
            id="hourSelect"
            class="pf-c-form-control"
            v-model="cronReturnArray.hour"
            @change="onChangeHour">
            <option
              value="--"
              disabled>
              -- Select an option --
            </option>
            <option value="*">Every hour (*)</option>
            <option value="*/2">Every other hour (*/2)</option>
            <option value="*/3">Every 3 hours (*/3)</option>
            <option value="*/4">Every 4 hours (*/4)</option>
            <option value="*/6">Every 6 hours (*/6)</option>
            <option value="0,12">Every 12 hours (0,12)</option>
            <option
              value="--"
              disabled>
              -- Hours --
            </option>
            <option value="0">12:00 a.m. midnight (0)</option>
            <option value="1">1:00 a.m. (1)</option>
            <option value="2">2:00 a.m. (2)</option>
            <option value="3">3:00 a.m. (3)</option>
            <option value="4">4:00 a.m. (4)</option>
            <option value="5">5:00 a.m. (5)</option>
            <option value="6">6:00 a.m. (6)</option>
            <option value="7">7:00 a.m. (7)</option>
            <option value="8">8:00 a.m. (8)</option>
            <option value="9">9:00 a.m. (9)</option>
            <option value="10">10:00 a.m. (10)</option>
            <option value="11">11:00 a.m. (11)</option>
            <option value="12">12:00 p.m. noon (12)</option>
            <option value="13">1:00 p.m. (13)</option>
            <option value="14">2:00 p.m. (14)</option>
            <option value="15">3:00 p.m. (15)</option>
            <option value="16">4:00 p.m. (16)</option>
            <option value="17">5:00 p.m. (17)</option>
            <option value="18">6:00 p.m. (18)</option>
            <option value="19">7:00 p.m. (19)</option>
            <option value="20">8:00 p.m. (20)</option>
            <option value="21">9:00 p.m. (21)</option>
            <option value="22">10:00 p.m. (22)</option>
            <option value="23">11:00 p.m. (23)</option>
          </select>
        </div>
      </div>
    </div>

    <!--DAYS-->
    <div class="pf-c-form__group pf-u-pt-md">
      <div class="pf-c-form__group-label">
        <label
          class="pf-c-form__label"
          for="form-demo-basic-name">
          <span class="pf-c-form__label-text">Day</span>
          <span
            class="pf-c-form__label-required"
            aria-hidden="true">
            &#42;
          </span>
        </label>
      </div>
      <div class="pf-c-form__group-control pf-l-grid pf-m-gutter">
        <div class="pf-l-grid__item pf-m-3-col">
          <input
            type="text"
            name="day"
            id="day"
            v-model="cronReturnArray.day"
            autocomplete="off"
            spellcheck="false"
            class="pf-c-form-control"
            @input="onChangeDay" />
        </div>
        <div class="pf-l-grid__item pf-m-9-col">
          <select
            id="daySelect"
            class="pf-c-form-control"
            v-model="cronReturnArray.day"
            @change="onChangeDay">
            <option
              value="--"
              disabled>
              -- Select an option --
            </option>
            <option value="*">Every day (*)</option>
            <option value="*/2">Every other day (*/2)</option>
            <option value="1,15">1st and 15th (1,15)</option>
            <option
              value="--"
              disabled>
              -- Days --
            </option>
            <option value="1">1st (1)</option>
            <option value="2">2nd (2)</option>
            <option value="3">3rd (3)</option>
            <option value="4">4th (4)</option>
            <option value="5">5th (5)</option>
            <option value="6">6th (6)</option>
            <option value="7">7th (7)</option>
            <option value="8">8th (8)</option>
            <option value="9">9th (9)</option>
            <option value="10">10th (10)</option>
            <option value="11">11th (11)</option>
            <option value="12">12th (12)</option>
            <option value="13">13th (13)</option>
            <option value="14">14th (14)</option>
            <option value="15">15th (15)</option>
            <option value="16">16th (16)</option>
            <option value="17">17th (17)</option>
            <option value="18">18th (18)</option>
            <option value="19">19th (19)</option>
            <option value="20">20th (20)</option>
            <option value="21">21st (21)</option>
            <option value="22">22nd (22)</option>
            <option value="23">23rd (23)</option>
            <option value="24">24th (24)</option>
            <option value="25">25th (25)</option>
            <option value="26">26th (26)</option>
            <option value="27">27th (27)</option>
            <option value="28">28th (28)</option>
            <option value="29">29th (29)</option>
            <option value="30">30th (30)</option>
            <option value="31">31st (31)</option>
          </select>
        </div>
      </div>
    </div>

    <!--MONTHS-->
    <div class="pf-c-form__group pf-u-pt-md">
      <div class="pf-c-form__group-label">
        <label
          class="pf-c-form__label"
          for="form-demo-basic-name">
          <span class="pf-c-form__label-text">Month</span>
          <span
            class="pf-c-form__label-required"
            aria-hidden="true">
            &#42;
          </span>
        </label>
      </div>
      <div class="pf-c-form__group-control pf-l-grid pf-m-gutter">
        <div class="pf-l-grid__item pf-m-3-col">
          <input
            type="text"
            name="month"
            id="month"
            v-model="cronReturnArray.month"
            autocomplete="off"
            spellcheck="false"
            class="pf-c-form-control"
            @input="onChangeMonth" />
        </div>
        <div class="pf-l-grid__item pf-m-9-col">
          <select
            id="monthSelect"
            class="pf-c-form-control"
            v-model="cronReturnArray.month"
            @change="onChangeMonth">
            <option
              value="--"
              disabled>
              -- Select an option --
            </option>
            <option value="*">Every month (*)</option>
            <option value="*/2">Every other month (*/2)</option>
            <option value="*/4">Every 3 months (*/4)</option>
            <option value="1,7">Every 6 months (1,7)</option>
            <option
              value="--"
              disabled>
              -- Months --
            </option>
            <option value="1">January (1)</option>
            <option value="2">February (2)</option>
            <option value="3">March (3)</option>
            <option value="4">April (4)</option>
            <option value="5">May (5)</option>
            <option value="6">June (6)</option>
            <option value="7">July (7)</option>
            <option value="8">August (8)</option>
            <option value="9">September (9)</option>
            <option value="10">October (10)</option>
            <option value="11">November (11)</option>
            <option value="12">December (12)</option>
          </select>
        </div>
      </div>
    </div>

    <!--WEEKDAYS-->
    <div class="pf-c-form__group pf-u-pt-md">
      <div class="pf-c-form__group-label">
        <label
          class="pf-c-form__label"
          for="form-demo-basic-name">
          <span class="pf-c-form__label-text">Weekday</span>
          <span
            class="pf-c-form__label-required"
            aria-hidden="true">
            &#42;
          </span>
        </label>
      </div>
      <div class="pf-c-form__group-control pf-l-grid pf-m-gutter">
        <div class="pf-l-grid__item pf-m-3-col">
          <input
            type="text"
            name="weekday"
            id="weekday"
            v-model="cronReturnArray.weekday"
            autocomplete="off"
            spellcheck="false"
            class="pf-c-form-control"
            @change="onChangeWeekday" />
        </div>
        <div class="pf-l-grid__item pf-m-9-col">
          <select
            id="weekdaySelect"
            class="pf-c-form-control"
            v-model="cronReturnArray.weekday"
            @change="onChangeWeekday">
            <option
              value="--"
              disabled>
              -- Select an option --
            </option>
            <option value="*">Every weekday (*)</option>
            <option value="1-5">Mon thru Fri (1-5)</option>
            <option value="0,6">Sat and Sun (6,0)</option>
            <option value="1,3,5">Mon, Wed, Fri (1,3,5)</option>
            <option value="2,4">Tues, Thurs (2,4)</option>
            <option value="2,5">Tues, Fri (2,5)</option>
            <option
              value="--"
              disabled>
              -- Weekdays --
            </option>
            <option value="0">Sunday (0)</option>
            <option value="1">Monday (1)</option>
            <option value="2">Tuesday (2)</option>
            <option value="3">Wednesday (3)</option>
            <option value="4">Thursday (4)</option>
            <option value="5">Friday (5)</option>
            <option value="6">Saturday (6)</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { reactive, watchEffect } from 'vue';

export default {
  props: {
    cronProp: {
      type: Array
    }
  },
  components: {},

  setup(props) {
    const errors = reactive({
      minute: '',
      hour: '',
      day: '',
      month: '',
      weekday: ''
    });
    const cronReturnArray = reactive({
      minute: props.cronProp[0] ? props.cronProp[0] : '*',
      hour: props.cronProp[1] ? props.cronProp[1] : '*',
      day: props.cronProp[2] ? props.cronProp[2] : '*',
      month: props.cronProp[3] ? props.cronProp[3] : '*',
      weekday: props.cronProp[4] ? props.cronProp[4] : '*'
    });

    function onChangeMin() {
      if (!cronReturnArray.minute) {
        errors.minute = 'Minute is required';
      } else {
        props.cronProp[0] = cronReturnArray.minute;
        errors.minute = '';
      }
    }
    function onChangeHour() {
      if (!cronReturnArray.hour) {
        errors.hour = 'Hour is required';
      } else {
        props.cronProp[1] = cronReturnArray.hour;
        errors.hour = '';
      }
    }
    function onChangeDay() {
      if (!cronReturnArray.day) {
        errors.day = 'Day is required';
      } else {
        props.cronProp[2] = cronReturnArray.day;
        errors.day = '';
      }
    }
    function onChangeMonth() {
      if (!cronReturnArray.month) {
        errors.month = 'Month is required';
      } else {
        props.cronProp[3] = cronReturnArray.month;
        errors.month = '';
      }
    }
    function onChangeWeekday() {
      if (!cronReturnArray.weekday) {
        errors.weekday = 'Weekday is required';
      } else {
        props.cronProp[4] = cronReturnArray.weekday;
        errors.weekday = '';
      }
    }

    watchEffect(() => {
      cronReturnArray.minute = props.cronProp[0];
      cronReturnArray.hour = props.cronProp[1];
      cronReturnArray.day = props.cronProp[2];
      cronReturnArray.month = props.cronProp[3];
      cronReturnArray.weekday = props.cronProp[4];
      errors.minute = '';
      errors.hour = '';
      errors.day = '';
      errors.month = '';
      errors.weekday = '';
    });

    return {
      cronReturnArray,
      errors,
      onChangeMin,
      onChangeHour,
      onChangeDay,
      onChangeMonth,
      onChangeWeekday
    };
  }
};
</scscript