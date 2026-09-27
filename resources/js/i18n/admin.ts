import en from "./locales/en";
import type { BlogAdminLocale } from "./locales/en";
import zhCN from "./locales/zh-CN";

export type { BlogAdminLocale } from "./locales/en";

export const blogAdminMessages: Record<string, BlogAdminLocale> = {
  en,
  "zh-CN": zhCN,
};
