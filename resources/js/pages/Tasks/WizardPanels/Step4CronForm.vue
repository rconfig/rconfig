<script setup>
import { reactive, watchEffect, toRefs } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { defineProps } from 'vue';

const props = defineProps({
  cronProp: {
    type: Array
  }
});

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

const { minute, hour, day, month, weekday } = toRefs(cronReturnArray);
</script>

<template>
  <div>
    <div>errors: {{ errors }}</div>
    <div>Selections: {{ minute }}</div>
    <div>cronReturnArray: {{ cronReturnArray }}</div>
    <br />

    <!--MINUTES-->
    <div class="flex w-full max-w-sm items-center gap-1.5">
      <Label
        class="w-1/4"
        for="picture">
        Select Minute
      </Label>
      <Input
        class="w-1/4"
        v-model="minute"
        @change="onChangeMin" />
      <Select
        v-model="minute"
        class="w-1/2">
        <SelectTrigger class="w-full">
          <SelectValue placeholder="Select an example cron option.." />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            <SelectLabel
              class="text-muted-foreground"
              value="--">
              -- Select an SelectItem --
            </SelectLabel>
            <SelectItem value="*">Every minute (*)</SelectItem>
            <SelectItem value="*/2">Every other minute (*/2)</SelectItem>
            <SelectItem value="*/5">Every 5 minutes (*/5)</SelectItem>
            <SelectItem value="*/10">Every 10 minutes (*/10)</SelectItem>
            <SelectItem value="*/15">Every 15 minutes (*/15)</SelectItem>
            <SelectItem value="0,30">Every 30 minutes (0,30)</SelectItem>
            <SelectLabel
              class="text-muted-foreground"
              value="--">
              -- Minutes --
            </SelectLabel>
            <SelectItem value="0">:00 top of the hour (0)</SelectItem>
            <SelectItem value="1">:01 (1)</SelectItem>
            <SelectItem value="2">:02 (2)</SelectItem>
            <SelectItem value="3">:03 (3)</SelectItem>
            <SelectItem value="4">:04 (4)</SelectItem>
            <SelectItem value="5">:05 (5)</SelectItem>
            <SelectItem value="6">:06 (6)</SelectItem>
            <SelectItem value="7">:07 (7)</SelectItem>
            <SelectItem value="8">:08 (8)</SelectItem>
            <SelectItem value="9">:09 (9)</SelectItem>
            <SelectItem value="10">:10 (10)</SelectItem>
            <SelectItem value="11">:11 (11)</SelectItem>
            <SelectItem value="12">:12 (12)</SelectItem>
            <SelectItem value="13">:13 (13)</SelectItem>
            <SelectItem value="14">:14 (14)</SelectItem>
            <SelectItem value="15">:15 quarter past (15)</SelectItem>
            <SelectItem value="16">:16 (16)</SelectItem>
            <SelectItem value="17">:17 (17)</SelectItem>
            <SelectItem value="18">:18 (18)</SelectItem>
            <SelectItem value="19">:19 (19)</SelectItem>
            <SelectItem value="20">:20 (20)</SelectItem>
            <SelectItem value="21">:21 (21)</SelectItem>
            <SelectItem value="22">:22 (22)</SelectItem>
            <SelectItem value="23">:23 (23)</SelectItem>
            <SelectItem value="24">:24 (24)</SelectItem>
            <SelectItem value="25">:25 (25)</SelectItem>
            <SelectItem value="26">:26 (26)</SelectItem>
            <SelectItem value="27">:27 (27)</SelectItem>
            <SelectItem value="28">:28 (28)</SelectItem>
            <SelectItem value="29">:29 (29)</SelectItem>
            <SelectItem value="30">:30 half past (30)</SelectItem>
            <SelectItem value="31">:31 (31)</SelectItem>
            <SelectItem value="32">:32 (32)</SelectItem>
            <SelectItem value="33">:33 (33)</SelectItem>
            <SelectItem value="34">:34 (34)</SelectItem>
            <SelectItem value="35">:35 (35)</SelectItem>
            <SelectItem value="36">:36 (36)</SelectItem>
            <SelectItem value="37">:37 (37)</SelectItem>
            <SelectItem value="38">:38 (38)</SelectItem>
            <SelectItem value="39">:39 (39)</SelectItem>
            <SelectItem value="40">:40 (40)</SelectItem>
            <SelectItem value="41">:41 (41)</SelectItem>
            <SelectItem value="42">:42 (42)</SelectItem>
            <SelectItem value="43">:43 (43)</SelectItem>
            <SelectItem value="44">:44 (44)</SelectItem>
            <SelectItem value="45">:45 quarter til (45)</SelectItem>
            <SelectItem value="46">:46 (46)</SelectItem>
            <SelectItem value="47">:47 (47)</SelectItem>
            <SelectItem value="48">:48 (48)</SelectItem>
            <SelectItem value="49">:49 (49)</SelectItem>
            <SelectItem value="50">:50 (50)</SelectItem>
            <SelectItem value="51">:51 (51)</SelectItem>
            <SelectItem value="52">:52 (52)</SelectItem>
            <SelectItem value="53">:53 (53)</SelectItem>
            <SelectItem value="54">:54 (54)</SelectItem>
            <SelectItem value="55">:55 (55)</SelectItem>
            <SelectItem value="56">:56 (56)</SelectItem>
            <SelectItem value="57">:57 (57)</SelectItem>
            <SelectItem value="58">:58 (58)</SelectItem>
            <SelectItem value="59">:59 (59)</SelectItem>
          </SelectGroup>
        </SelectContent>
      </Select>
    </div>
    <!--MINUTES-->

    <!--HOURS-->
    <!--HOURS-->
  </div>
</template>
