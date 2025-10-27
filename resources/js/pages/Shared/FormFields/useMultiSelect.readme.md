Absolutely — here's a clear and developer-friendly **README-style doc** explaining how your `useMultiSelect` composable (and its related components) should behave: how it accepts, transforms, and emits data in both **single** and **multi-select** modes.

---

## 🧠 `useMultiSelect` Developer Guide

This guide outlines how to **use**, **integrate**, and **understand** the data expectations for `useMultiSelect`, both in single-select and multi-select modes.

---

### 📦 Overview

`useMultiSelect` is a composable designed to power a select/dropdown component with:

* Optional **search**
* Optional **single/multi-select** logic
* Clean emit support via `v-model`-style usage

---

### 💡 Props and Setup

```ts
const { selectedItems, open, searchTerm, filteredItems, selectItem, deleteItem } = useMultiSelect({
	items,             // ref([]) - list of all options (objects with an `id` field)
	modelValue,        // current selected item(s) passed from parent
	singleSelect,      // true or false
	displayField,      // the field used for filtering and display (e.g., "name")
	searchFields,      // optional: array of fields to match against search input
	emit               // the emit function from setup
});
```

---

### 📥 Input: `modelValue` from Parent

#### ✅ Multi-select mode (`singleSelect: false`)

* Accepts an `Array<Object>`, e.g.:

```js
[
  { id: 1, name: "Cisco" },
  { id: 2, name: "Juniper" }
]
```

* `modelValue` should be a **stable array**, ideally using `v-model`.

---

#### ✅ Single-select mode (`singleSelect: true`)

* Accepts a **single object**, e.g.:

```js
{ id: 1, name: "Cisco" }
```

---

### 📤 Output: `emit("update:modelValue", ...)`

#### ✅ Multi-select mode

* Emits updated array of selected items:

```js
emit("update:modelValue", [item1, item2]);
```

* Called on:

  * item selection (`selectItem`)
  * item removal (`deleteItem`)

#### ✅ Single-select mode

* Emits a single object:

```js
emit("update:modelValue", item);
```

* Emits `{}` when deselected.

---

### 🔁 Data Shape Requirements

All items in `items` **must** be objects with a unique `id` field:

```ts
{ id: number | string, [displayField]: string, ... }
```

You can customize what field is used to display/search by passing `displayField` and `searchFields`.

---

### 🧪 Expected v-model Usage in Components

#### ✅ Multi-select (Array of objects)

```vue
<VendorMultiSelect v-model="model.device_vendor" />
```

```ts
const model = ref({
  device_vendor: []
});
```

---

#### ✅ Single-select (Object)

```vue
<CategorySelect :singleSelect="true" v-model="model.selectedCategory" />
```

```ts
const model = ref({
  selectedCategory: {}
});
```

---

### 🚫 Gotchas

* `modelValue` should always be **defined** (avoid `undefined`) — default to `[]` or `{}` in the parent.
* `items` must be a **ref** for reactivity to work.
* `id` **must** exist on all items. It’s used for filtering and deduplication.
* If you're seeing no selections being initialized, check that `modelValue` and `items` are populated **before** rendering.

---

### ✅ Summary

| Mode          | Input (from parent) | Output (emit)                       |
| ------------- | ------------------- | ----------------------------------- |
| Multi-select  | `Array<Object>`     | `emit("update:modelValue", Array)`  |
| Single-select | `Object`            | `emit("update:modelValue", Object)` |

---

Let me know if you'd like a section for Vue components that wrap this composable, or if you want inline examples for dropdown UIs.
