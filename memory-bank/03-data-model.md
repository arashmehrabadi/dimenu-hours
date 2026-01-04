# Data Model (Option schema)

Option key:
- dimenu_hours_settings

Shape (array stored in wp_options):
{
  version: "0.1.0",
  enabled: true,
  manual_closed: false,
  manual_closed_until: "",

  timezone: "Asia/Tehran" | "UTC" | ...,

  weekly: [
    { day: 0, is_closed: false, intervals: [{start:"09:00", end:"21:00"}, ...] },
    { day: 1, is_closed: false, intervals: [...] },
    ...
    { day: 6, is_closed: false, intervals: [...] }
  ],

  exceptions: [
    // future:
    // { type:"closed", date:"YYYY-MM-DD", note:"..." }
    // { type:"special_hours", date:"YYYY-MM-DD", intervals:[{start,end}], note:"..." }
    // { type:"range_closed", start_date:"YYYY-MM-DD", end_date:"YYYY-MM-DD", note:"..." }
    // { type:"range_special_hours", start_date:"YYYY-MM-DD", end_date:"YYYY-MM-DD", intervals:[{start,end}], note:"..." }
  ],

  messages: {
    closed_now: "الان بسته‌ایم.",
    open_now: "الان باز هستیم.",
    next_open_prefix: "سفارش‌گیری از",
    until_prefix: "تا",
    manual_closed: "فعلاً پذیرش سفارش غیرفعال است."
  },

  ui: {
    gate_add_to_cart: true,
    gate_checkout: true,
    show_banner: true
  }
}

Priority logic for OPEN/CLOSED (source of truth):
1) enabled==false => OPEN (no gating)
2) manual_closed==true => CLOSED
3) exceptions (future) => override weekly
4) weekly schedule => open/closed
