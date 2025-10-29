<script setup>
import Step4CronFormI18N from "@/i18n/pages/Tasks/WizardPanels/Step4CronForm.i18n.js";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from "@/components/ui/select";
import { reactive, watchEffect, toRefs, watch, ref } from "vue";
import { useComponentTranslations } from "@/composables/useComponentTranslations";

const emit = defineEmits(["update:modelValue"]);

const props = defineProps({
	modelValue: {
		type: Array,
		required: true,
	},
});

const errors = ref("");

const cronReturnArray = reactive({
	minute: props.modelValue[0] ? props.modelValue[0] : "*",
	hour: props.modelValue[1] ? props.modelValue[1] : "*",
	day: props.modelValue[2] ? props.modelValue[2] : "*",
	month: props.modelValue[3] ? props.modelValue[3] : "*",
	weekday: props.modelValue[4] ? props.modelValue[4] : "*",
});

const { minute, hour, day, month, weekday } = toRefs(cronReturnArray);

const { t } = useComponentTranslations(Step4CronFormI18N);

watch(
	() => [cronReturnArray.minute, cronReturnArray.hour, cronReturnArray.day, cronReturnArray.month, cronReturnArray.weekday],
	(newValue) => {
		if (newValue[0] === "" || newValue[1] === "" || newValue[2] === "" || newValue[3] === "" || newValue[4] === "") {
			errors.value = t("allFieldsRequired");
			return;
		}
		props.modelValue = newValue;
		// console.log('newValue', [cronReturnArray.minute, cronReturnArray.hour, cronReturnArray.day, cronReturnArray.month, cronReturnArray.weekday]);
		// console.log('props.modelValue', props.modelValue);
		updateParentModel();
	}
);

function updateParentModel() {
	emit("update:modelValue", [cronReturnArray.minute, cronReturnArray.hour, cronReturnArray.day, cronReturnArray.month, cronReturnArray.weekday]);
}

watchEffect(() => {
	cronReturnArray.minute = props.modelValue[0];
	cronReturnArray.hour = props.modelValue[1];
	cronReturnArray.day = props.modelValue[2];
	cronReturnArray.month = props.modelValue[3];
	cronReturnArray.weekday = props.modelValue[4];
	errors.value = "";
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
			<Label class="w-1/4" for="picture">
				{{ t("selectMinute") }}
			</Label>
			<Input class="w-1/4 focus:outline-none focus-visible:ring-0" v-model="cronReturnArray.minute" />
			<Select v-model="cronReturnArray.minute" class="w-1/2">
				<SelectTrigger class="w-full focus:outline-none focus:ring-0">
					<SelectValue :placeholder="t('selectAnOption')" />
				</SelectTrigger>
				<SelectContent>
					<SelectGroup>
						<SelectLabel class="text-muted-foreground" value="--">
							{{ t("selectAnOption") }}
						</SelectLabel>
						<SelectItem value="*">{{ t("everyMinute") }} (*)</SelectItem>
						<SelectItem value="*/2">{{ t("everyOtherMinute") }} (*/2)</SelectItem>
						<SelectItem value="*/5">{{ t("every5Minutes") }} (*/5)</SelectItem>
						<SelectItem value="*/10">{{ t("every10Minutes") }} (*/10)</SelectItem>
						<SelectItem value="*/15">{{ t("every15Minutes") }} (*/15)</SelectItem>
						<SelectItem value="0,30">{{ t("every30Minutes") }} (0,30)</SelectItem>
						<SelectLabel class="text-muted-foreground" value="--"> -- {{ t("minutes") }} -- </SelectLabel>
						<SelectItem value="0">:00 {{ t("topOfTheHour") }} (0)</SelectItem>
						<SelectItem value="1">{{ t("first") }} (1)</SelectItem>
						<SelectItem value="2">:02 (2)</SelectItem>
						<SelectItem value="3">:03 (3)</SelectItem>
						<SelectItem value="4">:04 (4)</SelectItem>
						<SelectItem value="5">{{ t("fifth") }} (5)</SelectItem>
						<SelectItem value="6">:06 (6)</SelectItem>
						<SelectItem value="7">:07 (7)</SelectItem>
						<SelectItem value="8">:08 (8)</SelectItem>
						<SelectItem value="9">:09 (9)</SelectItem>
						<SelectItem value="10">:10 (10)</SelectItem>
						<SelectItem value="11">:11 (11)</SelectItem>
						<SelectItem value="12">:12 (12)</SelectItem>
						<SelectItem value="13">:13 (13)</SelectItem>
						<SelectItem value="14">:14 (14)</SelectItem>
						<SelectItem value="15">:15 {{ t("quarterPast") }} (15)</SelectItem>
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
						<SelectItem value="30">:30 {{ t("halfPast") }} (30)</SelectItem>
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
						<SelectItem value="45">:45 {{ t("quarterTil") }} (45)</SelectItem>
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
			<Label class="w-1/4" for="picture">
				{{ t("selectHour") }}
			</Label>
			<Input class="w-1/4 focus:outline-none focus-visible:ring-0" v-model="cronReturnArray.hour" />
			<Select v-model="cronReturnArray.hour" class="w-1/2">
				<SelectTrigger class="w-full focus:outline-none focus:ring-0">
					<SelectValue :placeholder="t('selectAnOption')" />
				</SelectTrigger>
				<SelectContent>
					<SelectGroup>
						<SelectLabel class="text-muted-foreground" value="--">
							{{ t("selectAnOption") }}
						</SelectLabel>
						<SelectItem value="*">{{ t("everyHour") }} (*)</SelectItem>
						<SelectItem value="*/2">{{ t("everyOtherHour") }} (*/2)</SelectItem>
						<SelectItem value="*/3">{{ t("every3Hours") }} (*/3)</SelectItem>
						<SelectItem value="*/4">{{ t("every4Hours") }} (*/4)</SelectItem>
						<SelectItem value="*/6">{{ t("every6Hours") }} (*/6)</SelectItem>
						<SelectItem value="0,12">{{ t("every12Hours") }} (0,12)</SelectItem>
						<SelectLabel class="text-muted-foreground" value="--"> -- {{ t("hours") }} -- </SelectLabel>
						<SelectItem value="0">12:00 a.m. {{ t("midnight") }} (0)</SelectItem>
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
						<SelectItem value="12">12:00 p.m. {{ t("noon") }} (12)</SelectItem>
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
			<Label class="w-1/4" for="picture">
				{{ t("selectDay") }}
			</Label>
			<Input class="w-1/4 focus:outline-none focus-visible:ring-0" v-model="cronReturnArray.day" />
			<Select v-model="cronReturnArray.day" class="w-1/2">
				<SelectTrigger class="w-full focus:outline-none focus:ring-0">
					<SelectValue :placeholder="t('selectAnOption')" />
				</SelectTrigger>
				<SelectContent>
					<SelectGroup>
						<SelectLabel class="text-muted-foreground" value="--">
							{{ t("selectAnOption") }}
						</SelectLabel>
						<SelectItem value="*">{{ t("everyDay") }} (*)</SelectItem>
						<SelectItem value="*/2">{{ t("everyOtherDay") }} (*/2)</SelectItem>
						<SelectItem value="1,15">{{ t("firstAnd15th") }} (1,15)</SelectItem>
						<SelectLabel class="text-muted-foreground" value="--"> -- {{ t("days") }} -- </SelectLabel>
						<SelectItem value="1">{{ t("first") }} (1)</SelectItem>
						<SelectItem value="2">{{ t("second") }} (2)</SelectItem>
						<SelectItem value="3">{{ t("third") }} (3)</SelectItem>
						<SelectItem value="4">{{ t("fourth") }} (4)</SelectItem>
						<SelectItem value="5">{{ t("fifth") }} (5)</SelectItem>
						<SelectItem value="6">{{ t("sixth") }} (6)</SelectItem>
						<SelectItem value="7">{{ t("seventh") }} (7)</SelectItem>
						<SelectItem value="8">{{ t("eighth") }} (8)</SelectItem>
						<SelectItem value="9">{{ t("ninth") }} (9)</SelectItem>
						<SelectItem value="10">{{ t("tenth") }} (10)</SelectItem>
						<SelectItem value="11">{{ t("eleventh") }} (11)</SelectItem>
						<SelectItem value="12">{{ t("twelfth") }} (12)</SelectItem>
						<SelectItem value="13">{{ t("thirteenth") }} (13)</SelectItem>
						<SelectItem value="14">{{ t("fourteenth") }} (14)</SelectItem>
						<SelectItem value="15">{{ t("fifteenth") }} (15)</SelectItem>
						<SelectItem value="16">{{ t("sixteenth") }} (16)</SelectItem>
						<SelectItem value="17">{{ t("seventeenth") }} (17)</SelectItem>
						<SelectItem value="18">{{ t("eighteenth") }} (18)</SelectItem>
						<SelectItem value="19">{{ t("nineteenth") }} (19)</SelectItem>
						<SelectItem value="20">{{ t("twentieth") }} (20)</SelectItem>
						<SelectItem value="21">{{ t("twentyFirst") }} (21)</SelectItem>
						<SelectItem value="22">{{ t("twentySecond") }} (22)</SelectItem>
						<SelectItem value="23">{{ t("twentyThird") }} (23)</SelectItem>
						<SelectItem value="24">{{ t("twentyFourth") }} (24)</SelectItem>
						<SelectItem value="25">{{ t("twentyFifth") }} (25)</SelectItem>
						<SelectItem value="26">{{ t("twentySixth") }} (26)</SelectItem>
						<SelectItem value="27">{{ t("twentySeventh") }} (27)</SelectItem>
						<SelectItem value="28">{{ t("twentyEighth") }} (28)</SelectItem>
						<SelectItem value="29">{{ t("twentyNinth") }} (29)</SelectItem>
						<SelectItem value="30">{{ t("thirtieth") }} (30)</SelectItem>
						<SelectItem value="31">{{ t("thirtyFirst") }} (31)</SelectItem>
					</SelectGroup>
				</SelectContent>
			</Select>
		</div>
		<!--DAYS-->

		<!--MONTHS-->
		<div class="flex w-full max-w-xl items-center gap-1.5">
			<Label class="w-1/4" for="picture">
				{{ t("selectMonth") }}
			</Label>
			<Input class="w-1/4 focus:outline-none focus-visible:ring-0" v-model="cronReturnArray.month" />
			<Select v-model="cronReturnArray.month" class="w-1/2">
				<SelectTrigger class="w-full focus:outline-none focus:ring-0">
					<SelectValue :placeholder="t('selectAnOption')" />
				</SelectTrigger>
				<SelectContent>
					<SelectGroup>
						<SelectLabel class="text-muted-foreground" value="--">
							{{ t("selectAnOption") }}
						</SelectLabel>
						<SelectItem value="*">{{ t("everyMonth") }} (*)</SelectItem>
						<SelectItem value="*/2">{{ t("everyOtherMonth") }} (*/2)</SelectItem>
						<SelectItem value="*/4">{{ t("every3Months") }} (*/4)</SelectItem>
						<SelectItem value="1,7">{{ t("every6Months") }} (1,7)</SelectItem>
						<SelectLabel class="text-muted-foreground" value="--"> -- {{ t("months") }} -- </SelectLabel>
						<SelectItem value="1">{{ t("january") }} (1)</SelectItem>
						<SelectItem value="2">{{ t("february") }} (2)</SelectItem>
						<SelectItem value="3">{{ t("march") }} (3)</SelectItem>
						<SelectItem value="4">{{ t("april") }} (4)</SelectItem>
						<SelectItem value="5">{{ t("may") }} (5)</SelectItem>
						<SelectItem value="6">{{ t("june") }} (6)</SelectItem>
						<SelectItem value="7">{{ t("july") }} (7)</SelectItem>
						<SelectItem value="8">{{ t("august") }} (8)</SelectItem>
						<SelectItem value="9">{{ t("september") }} (9)</SelectItem>
						<SelectItem value="10">{{ t("october") }} (10)</SelectItem>
						<SelectItem value="11">{{ t("november") }} (11)</SelectItem>
						<SelectItem value="12">{{ t("december") }} (12)</SelectItem>
					</SelectGroup>
				</SelectContent>
			</Select>
		</div>
		<!--MONTHS-->

		<!--WEEKDAYS-->
		<div class="flex w-full max-w-xl items-center gap-1.5">
			<Label class="w-1/4" for="picture">
				{{ t("selectWeekday") }}
			</Label>
			<Input class="w-1/4 focus:outline-none focus-visible:ring-0" v-model="cronReturnArray.weekday" />
			<Select v-model="cronReturnArray.weekday" class="w-1/2">
				<SelectTrigger class="w-full focus:outline-none focus:ring-0">
					<SelectValue :placeholder="t('selectAnOption')" />
				</SelectTrigger>
				<SelectContent>
					<SelectGroup>
						<SelectLabel class="text-muted-foreground" value="--">
							{{ t("selectAnOption") }}
						</SelectLabel>
						<SelectItem value="*">{{ t("everyWeekday") }} (*)</SelectItem>
						<SelectItem value="1-5">{{ t("monThruFri") }} (1-5)</SelectItem>
						<SelectItem value="0,6">{{ t("satAndSun") }} (6,0)</SelectItem>
						<SelectItem value="1,3,5">{{ t("monWedFri") }} (1,3,5)</SelectItem>
						<SelectItem value="2,4">{{ t("tuesThurs") }} (2,4)</SelectItem>
						<SelectItem value="2,5">{{ t("tuesFri") }} (2,5)</SelectItem>
						<SelectLabel class="text-muted-foreground" value="--"> -- {{ t("weekday") }} -- </SelectLabel>
						<SelectItem value="0">{{ t("sunday") }} (0)</SelectItem>
						<SelectItem value="1">{{ t("monday") }} (1)</SelectItem>
						<SelectItem value="2">{{ t("tuesday") }} (2)</SelectItem>
						<SelectItem value="3">{{ t("wednesday") }} (3)</SelectItem>
						<SelectItem value="4">{{ t("thursday") }} (4)</SelectItem>
						<SelectItem value="5">{{ t("friday") }} (5)</SelectItem>
						<SelectItem value="6">{{ t("saturday") }} (6)</SelectItem>
					</SelectGroup>
				</SelectContent>
			</Select>
		</div>
		<!--WEEKDAYS-->
	</div>
</template>
