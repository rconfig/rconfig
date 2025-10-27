Here’s the **implementation notes** for the updated `NavTabs` with the subtle per-button indicator:

---

### **1. Purpose**

* Replace the old sliding underline logic with a **per-button underline** that’s always in the correct place, eliminating layout jank on load.
* Keep original `<Button>` styling, only adding a subtle indicator.
* Provide a smooth scale animation on selection change.

---

### **2. Key Features**

1. **Per-button indicator**

   * The indicator is an absolutely positioned `<span>` inside each button.
   * It animates by scaling on the X-axis from `scale-x-0` (hidden) to `scale-x-100` (visible).

2. **Extra width**

   * Using `-left-1` and `-right-1` on the indicator to make it \~4px wider on each side.

3. **No measurement logic**

   * No `getBoundingClientRect` or resize observers.
   * Always renders correctly because the indicator is bound to its button.

4. **Persistence (optional)**

   * If `persistKey` is set, the selected tab is saved in `localStorage` and restored on reload.

---

### **3. Props**

| Prop         | Type   | Description                                       |
| ------------ | ------ | ------------------------------------------------- |
| `items`      | Array  | Array of `{ label, to, icon? }` defining the tabs |
| `modelValue` | String | The currently active `to` value                   |
| `persistKey` | String | LocalStorage key to persist active tab            |

---

### **4. Emits**

* `update:modelValue` — fired when selection changes.
* `select` — fired with the selected `to` value for parent handling (e.g., navigation).

---

### **5. Usage Example**

```vue
<NavTabs
  :items="tabs"
  v-model="active"
  persist-key="settingsNetworkServicesActivePane"
  @select="setForm"
/>

<component :is="formComponents[active]" v-if="active" />
```

```js
const tabs = [
  { label: "XFTP", to: "/settings/xftp", icon: "xftp" },
  { label: "SNMP Traps", to: "/snmp-traps", icon: "snmp" },
];
```

---

### **6. Styling Notes**

* Uses Tailwind classes:

  * **`absolute -left-1 -right-1 -bottom-[6px] h-0.5 bg-blue-500`** → underline position & size.
  * **`origin-center transition-transform duration-200`** → animation smoothness.
  * **`scale-x-0` / `scale-x-100`** → toggles visibility with a scale animation.

---

If you want, I can also add **a small fade-in** to the indicator so the transition feels even softer. That would be a one-line tweak.
