<script setup>
import { reactive, watchEffect, toRefs, watch, ref } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
  modelValue: {
    type: Array,
    required: true
  }
});

const errors = ref('');

const cronReturnArray = reactive({
  minute: props.modelValue[0] ? props.modelValue[0] : '*',
  hour: props.modelValue[1] ? props.modelValue[1] : '*',
  day: props.modelValue[2] ? props.modelValue[2] : '*',
  month: props.modelValue[3] ? props.modelValue[3] : '*',
  weekday: props.modelValue[4] ? props.modelValue[4] : '*'
});

const { minute, hour, day, month, weekday } = toRefs(cronReturnArray);

watch(
  () => [cronReturnArray.minute, cronReturnArray.hour, cronReturnArray.day, cronReturnArray.month, cronReturnArray.weekday],
  newValue => {
    if (newValue[0] === '' || newValue[1] === '' || newValue[2] === '' || newValue[3] === '' || newValue[4] === '') {
      errors.value = 'All fields are required';
      return;
    }
    props.modelValue = newValue;
    // console.log('newValue', [cronReturnArray.minute, cronReturnArray.hour, cronReturnArray.day, cronReturnArray.month, cronReturnArray.weekday]);
    // console.log('props.modelValue', props.modelValue);
    updateParentModel();
  }
);

function updateParentModel() {
  emit('update:modelValue', [cronReturnArray.minute, cronReturnArray.hour, cronReturnArray.day, cronReturnArray.month, cronReturnArray.weekday]);
}

watchEffect(() => {
  cronReturnArray.minute = props.modelValue[0];
  cronReturnArray.hour = props.modelValue[1];
  cronReturnArray.day = props.modelValue[2];
  cronReturnArray.month = props.modelValue[3];
  cronReturnArray.weekday = props.modelValue[4];
  errors.value = '';
});
</script>

<template>
  <div>
    <div class="text-red-500">
      {{ errors }}
    </div>

    <br />

    <!--MINUTES-->
    <div class="flex w-full max-w-xl items-center gap-1.5">
      <Label
        class="w-1/4"
        for="picture">
        Select Minute
      </Label>
      <Input
        class="w-1/4 focus:outline-none focus-visible:ring-0"
        v-model="cronReturnArray.minute" />
      <Select
        v-model="cronReturnArray.minute"
        class="w-1/2">
        <SelectTrigger class="w-full focus:outline-none focus:ring-0">
          <SelectValue placeholder="Select an example cron option.." />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            <SelectLabel
              class="text-muted-foreground"
              value="--">
              -- Select an option --
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
    <div class="flex w-full max-w-xl items-center gap-1.5">
      <Label
        class="w-1/4"
        for="picture">
        Select Hour
      </Label>
      <Input
        class="w-1/4 focus:outline-none focus-visible:ring-0"
        v-model="cronReturnArray.hour" />
      <Select
        v-model="cronReturnArray.hour"
        class="w-1/2">
        <SelectTrigger class="w-full focus:outline-none focus:ring-0">
          <SelectValue placeholder="Select an example cron option.." />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            <SelectLabel
              class="text-muted-foreground"
              value="--">
              -- Select an option --
            </SelectLabel>
            <SelectItem value="*">Every hour (*)</SelectItem>
            <SelectItem value="*/2">Every other hour (*/2)</SelectItem>
            <SelectItem value="*/3">Every 3 hours (*/3)</SelectItem>
            <SelectItem value="*/4">Every 4 hours (*/4)</SelectItem>
            <SelectItem value="*/6">Every 6 hours (*/6)</SelectItem>
            <SelectItem value="0,12">Every 12 hours (0,12)</SelectItem>
            <SelectLabel
              class="text-muted-foreground"
              value="--">
              -- Hours --
            </SelectLabel>
            <SelectItem value="0">12:00 a.m. midnight (0)</SelectItem>
            <SelectItem value="1">1:00 a.m. (1)</SelectItem>
            <SelectItem value="2">2:00 a.m. (2)</SelectItem>
            <SelectItem value="3">3:00 a.m. (3)</SelectItem>
            <SelectItem value="4">4:00 a.m. (4)</SelectItem>
            <SelectItem value="5">5:00 a.m. (5)</SelectItem>
            <SelectItem value="6">6:00 a.m. (6)</SelectItem>
            <SelectItem value="7">7:00 a.m. (7)</SelectItem>
            <SelectItem value="8">8:00 a.m. (8)</SelectItem>
            <SelectItem value="9">9:00 a.m. (9)</SelectItem>
            <SelectItem value="10">10:00 a.m. (10)</SelectItem>
            <SelectItem value="11">11:00 a.m. (11)</SelectItem>
            <SelectItem value="12">12:00 p.m. noon (12)</SelectItem>
            <SelectItem value="13">1:00 p.m. (13)</SelectItem>
            <SelectItem value="14">2:00 p.m. (14)</SelectItem>
            <SelectItem value="15">3:00 p.m. (15)</SelectItem>
            <SelectItem value="16">4:00 p.m. (16)</SelectItem>
            <SelectItem value="17">5:00 p.m. (17)</SelectItem>
            <SelectItem value="18">6:00 p.m. (18)</SelectItem>
            <SelectItem value="19">7:00 p.m. (19)</SelectItem>
            <SelectItem value="20">8:00 p.m. (20)</SelectItem>
            <SelectItem value="21">9:00 p.m. (21)</SelectItem>
            <SelectItem value="22">10:00 p.m. (22)</SelectItem>
            <SelectItem value="23">11:00 p.m. (23)</SelectItem>
          </SelectGroup>
        </SelectContent>
      </Select>
    </div>
    <!--HOURS-->

    <!--DAYS-->
    <div class="flex w-full max-w-xl items-center gap-1.5">
      <Label
        class="w-1/4"
        for="picture">
        Select Day
      </Label>
      <Input
        class="w-1/4 focus:outline-none focus-visible:ring-0"
        v-model="cronReturnArray.day" />
      <Select
        v-model="cronReturnArray.day"
        class="w-1/2">
        <SelectTrigger class="w-full focus:outline-none focus:ring-0">
          <SelectValue placeholder="Select an example cron option.." />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            <SelectLabel
              class="text-muted-foreground"
              value="--">
              -- Select an option --
            </SelectLabel>
            <SelectItem value="*">Every day (*)</SelectItem>
            <SelectItem value="*/2">Every other day (*/2)</SelectItem>
            <SelectItem value="1,15">1st and 15th (1,15)</SelectItem>
            <SelectLabel
              class="text-muted-foreground"
              value="--">
              -- Days --
            </SelectLabel>
            <SelectItem value="1">1st (1)</SelectItem>
            <SelectItem value="2">2nd (2)</SelectItem>
            <SelectItem value="3">3rd (3)</SelectItem>
            <SelectItem value="4">4th (4)</SelectItem>
            <SelectItem value="5">5th (5)</SelectItem>
            <SelectItem value="6">6th (6)</SelectItem>
            <SelectItem value="7">7th (7)</SelectItem>
            <SelectItem value="8">8th (8)</SelectItem>
            <SelectItem value="9">9th (9)</SelectItem>
            <SelectItem value="10">10th (10)</SelectItem>
            <SelectItem value="11">11th (11)</SelectItem>
            <SelectItem value="12">12th (12)</SelectItem>
            <SelectItem value="13">13th (13)</SelectItem>
            <SelectItem value="14">14th (14)</SelectItem>
            <SelectItem value="15">15th (15)</SelectItem>
            <SelectItem value="16">16th (16)</SelectItem>
            <SelectItem value="17">17th (17)</SelectItem>
            <SelectItem value="18">18th (18)</SelectItem>
            <SelectItem value="19">19th (19)</SelectItem>
            <SelectItem value="20">20th (20)</SelectItem>
            <SelectItem value="21">21st (21)</SelectItem>
            <SelectItem value="22">22nd (22)</SelectItem>
            <SelectItem value="23">23rd (23)</SelectItem>
            <SelectItem value="24">24th (24)</SelectItem>
            <SelectItem value="25">25th (25)</SelectItem>
            <SelectItem value="26">26th (26)</SelectItem>
            <SelectItem value="27">27th (27)</SelectItem>
            <SelectItem value="28">28th (28)</SelectItem>
            <SelectItem value="29">29th (29)</SelectItem>
            <SelectItem value="30">30th (30)</SelectItem>
            <SelectItem value="31">31st (31)</SelectItem>
          </SelectGroup>
        </SelectContent>
      </Select>
    </div>
    <!--DAYS-->

    <!--MONTHS-->
    <div class="flex w-full max-w-xl items-center gap-1.5">
      <Label
        class="w-1/4"
        for="picture">
        Select Day
      </Label>
      <Input
        class="w-1/4 focus:outline-none focus-visible:ring-0"
        v-model="cronReturnArray.month" />
      <Select
        v-model="cronReturnArray.month"
        class="w-1/2">
        <SelectTrigger class="w-full focus:outline-none focus:ring-0">
          <SelectValue placeholder="Select an example cron option.." />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            <SelectLabel
              class="text-muted-foreground"
              value="--">
              -- Select an option --
            </SelectLabel>
            <SelectItem value="*">Every month (*)</SelectItem>
            <SelectItem value="*/2">Every other month (*/2)</SelectItem>
            <SelectItem value="*/4">Every 3 months (*/4)</SelectItem>
            <SelectItem value="1,7">Every 6 months (1,7)</SelectItem>
            <SelectLabel
              class="text-muted-foreground"
              value="--">
              -- Months --
            </SelectLabel>
            <SelectItem value="1">January (1)</SelectItem>
            <SelectItem value="2">February (2)</SelectItem>
            <SelectItem value="3">March (3)</SelectItem>
            <SelectItem value="4">April (4)</SelectItem>
            <SelectItem value="5">May (5)</SelectItem>
            <SelectItem value="6">June (6)</SelectItem>
            <SelectItem value="7">July (7)</SelectItem>
            <SelectItem value="8">August (8)</SelectItem>
            <SelectItem value="9">September (9)</SelectItem>
            <SelectItem value="10">October (10)</SelectItem>
            <SelectItem value="11">November (11)</SelectItem>
            <SelectItem value="12">December (12)</SelectItem>
          </SelectGroup>
        </SelectContent>
      </Select>
    </div>
    <!--MONTHS-->

    <!--WEEKDAYS-->
    <div class="flex w-full max-w-xl items-center gap-1.5">
      <Label
        class="w-1/4"
        for="picture">
        Select Day
      </Label>
      <Input
        class="w-1/4 focus:outline-none focus-visible:ring-0"
        v-model="cronReturnArray.weekday" />
      <Select
        v-model="cronReturnArray.weekday"
        class="w-1/2">
        <SelectTrigger class="w-full focus:outline-none focus:ring-0">
          <SelectValue placeholder="Select an example cron option.." />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            <SelectLabel
              class="text-muted-foreground"
              value="--">
              -- Select an option --
            </SelectLabel>
            <SelectItem value="*">Every weekday (*)</SelectItem>
            <SelectItem value="1-5">Mon thru Fri (1-5)</SelectItem>
            <SelectItem value="0,6">Sat and Sun (6,0)</SelectItem>
            <SelectItem value="1,3,5">Mon, Wed, Fri (1,3,5)</SelectItem>
            <SelectItem value="2,4">Tues, Thurs (2,4)</SelectItem>
            <SelectItem value="2,5">Tues, Fri (2,5)</SelectItem>
            <SelectLabel
              class="text-muted-foreground"
              value="--">
              -- Weekday --
            </SelectLabel>
            <SelectItem value="0">Sunday (0)</SelectItem>
            <SelectItem value="1">Monday (1)</SelectItem>
            <SelectItem value="2">Tuesday (2)</SelectItem>
            <SelectItem value="3">Wednesday (3)</SelectItem>
            <SelectItem value="4">Thursday (4)</SelectItem>
            <SelectItem value="5">Friday (5)</SelectItem>
            <SelectItem value="6">Saturday (6)</SelectItem>
          </SelectGroup>
        </SelectContent>
      </Select>
    </div>
    <!--WEEKDAYS-->
  </div>
</template>
